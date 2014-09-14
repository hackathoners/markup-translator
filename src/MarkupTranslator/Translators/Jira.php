<?php

namespace MarkupTranslator\Translators;

class Jira extends Base
{

    const MATCH_HEADING = '/h([1-6]{1})\.\s+([^$]+)/';
    const MATCH_HR = '----';
    const MATCH_LINK = '/(http|https):\/\/[^$\s\]]+/';

    const BLOCKQUOTE_INLINE_START = 'bq. ';
    const BLOCKQUOTE_BLOCK_START = "{quote}\n";
    const EMPHASIZED_START_END = '_';
    const STRONG_START_END = '*';

    protected function getMarkupName()
    {
        return 'jira';
    }

    /**
     * @param  \XMLReader $xml an XML document to be transformed to GitHub Markdown
     * @return String
     */
    protected function processXml($xml)
    {
        $output = '';

        // states
        $inLink = false;

        /**
         * Mapping of elements which just append additional markup to the text
         */
        $appendersMap = [
            self::NODE_HR => '----',
            self::NODE_BR => "\n",
            self::NODE_H1 => 'h1. ',
            self::NODE_H2 => 'h2. ',
            self::NODE_H3 => 'h3. ',
            self::NODE_H4 => 'h4. ',
            self::NODE_H5 => 'h5. ',
            self::NODE_H6 => 'h6. ',
            self::NODE_BLOCKQUOTE => 'bq. ',
        ];

        /**
         * Mapping of elements which wrap text with additional markup
         */
        $wrappersMap = [
            self::NODE_EMPHASIZED => '_',
            self::NODE_STRONG => '*',
        ];

        while ($xml->read()) {
            // if it's a beginning of new paragraph just continue
            if ($xml->nodeType === \XMLReader::ELEMENT && $xml->name === self::NODE_PARAGRAPH) {
                continue;
            }

            // if it's an ending of a paragraph add newlines
            if ($xml->nodeType === \XMLReader::END_ELEMENT && $xml->name === self::NODE_PARAGRAPH) {
                $output .= "\n\n";
            }

            // take care of wrapping nodes
            if (in_array($xml->name, array_keys($wrappersMap))) {
                $output .= $wrappersMap[$xml->name];
            }

            // take care of nodes which just append things to their text
            if (in_array($xml->name, array_keys($appendersMap)) && $xml->nodeType !== \XMLReader::END_ELEMENT) {
                $output .= $appendersMap[$xml->name];
            }

            // links
            if ($xml->name === self::NODE_A && $xml->nodeType !== \XMLReader::END_ELEMENT) {
                $output .= '[';
                $inLink = true;
            }

            if ($xml->name === self::NODE_A && $xml->nodeType === \XMLReader::END_ELEMENT) {
                $output .= $xml->getAttribute('href') . ']';
                $inLink = false;
            }

            // just add the text
            if ($xml->nodeType === \XMLReader::TEXT) {
                $text = $xml->readString();

                if ($inLink && !empty($text) )
                {
                    $output .= $text . '|' ;
                }
                else
                {
                    $output .= $text;
                }
            }
        }

        return trim($output);
    }

    /**
     * Processing block elements. Block elements are:
     * - paragraph
     * - heading
     * - horizontal rule
     * - blockquote (multiline)
     * - code (multiline)
     *
     * @param  string $text text to be proccessed
     * @return string
     */
    protected function processBlock($text)
    {
        if (preg_match(self::MATCH_HEADING, $text, $matches)) {
            return $this->addHeading($matches[1], $matches[2]);
        }

        if (strpos($text, self::MATCH_HR) !== false) {
            return $this->addHorizontalRule($text);
        }

        if (mb_substr($text, 0, strlen(self::BLOCKQUOTE_BLOCK_START)) === self::BLOCKQUOTE_BLOCK_START) {
            return $this->processBlockquote($text);
        }

        if (mb_substr($text, 0, strlen(self::BLOCKQUOTE_INLINE_START)) === self::BLOCKQUOTE_INLINE_START) {
            return $this->processBlockquote($text, true);
        }

        // nothing found wrap in paragraph
        return $this->processParagraph($text);
    }

    /**
     * Processes text wrapped in markup saying it's emphasized
     *
     * @param  string $text   text to be processed into a <em/> tag
     * @param  bool   $inline flag telling if it's an inline blockquote or not
     * @return string
     */
    private function processBlockquote($text, $inline = false)
    {
        if ($inline) {
            $length = 0;
            $text = str_replace(self::BLOCKQUOTE_INLINE_START, '', $text); // remove bq.
        } else {
            $length = strlen(self::BLOCKQUOTE_BLOCK_START);
            $text = mb_substr($text, $length); // remove first {quote}
        }

        $end = $this->findBlockquoteEnd($text, $inline);
        $blockquote = mb_substr($text, 0, $end);
        $this->wrapInNode(self::NODE_BLOCKQUOTE, function () use ($blockquote) {
            $this->processInline($blockquote);
        });

        return mb_substr($text, $end + $length);
    }

    /**
     * Looks for an end of blockqoute and returns the possition of the end
     *
     * @param  string $text   text being processed as blockqoute block
     * @param  bool   $inline
     * @return int
     */
    private function findBlockquoteEnd($text, $inline = false)
    {
        $end = $this->lookAhead($text, ($inline ? "\n" : self::BLOCKQUOTE_BLOCK_START));

        if ($end === false && !$inline) {
            // the text may include only blockqoute and no new line at the end
            $end = $this->lookAhead($text, str_replace("\n", '', self::BLOCKQUOTE_BLOCK_START));
        }

        if ($end === false) {
            $end = mb_strlen($text);
        }

        return $end;
    }

    /**
     * Processes text wrapped in markup saying it's emphasized
     *
     * @param  string $text text to be processed into a <em/> tag
     * @return string
     */
    private function processEmphasized($text)
    {
        return $this->processWrapper($text, self::EMPHASIZED_START_END, self::NODE_EMPHASIZED);
    }

    /**
     * Processes text wrapped in markup saying it's important
     *
     * @param  string $text text to be processed into a <strong/> tag
     * @return string
     */
    private function processStrong($text)
    {
        return $this->processWrapper($text, self::STRONG_START_END, self::NODE_STRONG);
    }

    /**
     * Common logic for wrapping markup such as <em/> and <strong/> tags
     *
     * @param string $text             text to be processed
     * @param string $formattingMarkup markup to process; one of class constants such as:
     *                                 EMPHASIZED_START_END or STRONG_START_END
     * @param string $nodeTag          XML tag to be produced; one of class constants such as:
     *                                 NODE_EMPHASIZED or NODE_STRONG
     * @param int    $markupLength
     *
     * @return string
     */
    private function processWrapper($text, $formattingMarkup, $nodeTag, $markupLength = 1)
    {
        $formattedTextBegin = $this->lookAhead($text, $formattingMarkup);
        $beforeFormattedText = mb_substr($text, 0, $formattedTextBegin);
        $formattedTextEnd = $this->lookAhead(mb_substr($text, $formattedTextBegin + $markupLength), $formattingMarkup);
        $afterFormattedText = mb_substr(mb_substr($text, $formattedTextBegin + $markupLength), $formattedTextEnd + $markupLength);
        $formattedText = mb_substr($text, $formattedTextBegin + $markupLength, $formattedTextEnd);

        if ($beforeFormattedText !== '') {
            $this->text($beforeFormattedText);
        }

        $this->wrapInNode($nodeTag, function () use ($formattedText) {
            $this->processInline($formattedText);
        });

        if ($afterFormattedText !== '') {
            $this->text($afterFormattedText);
        }

        return '';
    }

    /**
     * Processes text with a link markup
     *
     * @param string $text text with a link
     * @param string $link the link gathered from preg_match()
     * @return string
     */
    private function processLink($text, $link) {
        $link = rtrim($link, '.');
        $pipe = explode('|', $text);

        if (mb_strlen($pipe[0]) !== mb_strlen($text)) {
            $pipe = explode('[', $pipe[0]);
            $linkText = $pipe[1];
            $search = '[' . $linkText . '|' . $link . ']';
            $replace = sprintf('<a href="%s">%s</a>', $link, $linkText);
        } else {
            $search = $link;
            $replace = sprintf('<a href="%s">%s</a>', $link, $link);
        }

        $this->writeRaw(str_replace($search, $replace, $text));

        return '';
    }

    /** Inline elements are:
     * - emphasiezed
     * - strong
     * - links
     * - images
     * - emoticons
     *
     * @param string $text
     *
     * @return string
     */
    protected function processInline($text)
    {
        while($text) {
            if (preg_match(self::MATCH_LINK, $text, $m)) {
                return $this->processLink($text, $m[0]);
            }

            if( $this->lookAhead($text, self::STRONG_START_END) !== false ) {
                return $this->processStrong($text);
            }

            if ( $this->lookAhead($text, self::EMPHASIZED_START_END) !== false ) {
                return $this->processEmphasized($text);
            }

            $text = $this->processRestOfLine($text);
        }

        return ''; //All text is consumed
    }

}
