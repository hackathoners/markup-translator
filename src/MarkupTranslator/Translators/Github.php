<?php

namespace MarkupTranslator\Translators;

class Github extends Base
{

    const EOL = "\n";
    const BLOCKQUOTE_START = '> ';
    const EMPHASIZED_START_END = '*';
    const STRONG_START_END = '**';
    const EMPHASIZED_START_END_TYPE_2 = '_';
    const STRONG_START_END_TYPE_2 = '__';
    const MATCH_HEADING = '/([#]{1,6})\s+([^$]+)/';
    const MATCH_HR = '/^\s*([\-]{3,}|[\*]{3,})|[\- ]{5,}|[\* ]{5,}\s*$/';
    const MATCH_LINK = '/(.+)\[([^\]]+)\]\(([^\)]+)\)(.+)/';

    protected $stateMachine = [
        'inBlockQuote' => false,
        'inEmphasized' => false,
        'inStrong' => false,
    ];

    protected function getMarkupName() {
        return 'GitHub Markdown';
    }

    /**
     * Block elements are:
     * - paragraph
     * - heading
     * - horizontal rule
     * - blockquote (multiline)
     * - code (multiline)
     */
    protected function processBlock($text)
    {
        // Process heading
        if (preg_match(self::MATCH_HEADING, $text, $matches)) {
            return $this->addHeading(strlen($matches[1]), $matches[2]);
        }

        if (preg_match(self::MATCH_HR, $text)) {
            return $this->addHorizontalRule($text);
        }

        if (mb_substr($text, 0, 2) === self::BLOCKQUOTE_START) {
            return $this->processBlockquote($text);
        }

        // nothing found wrap in paragraph
        return $this->processParagraph($text);
   }

    /** Inline elements are:
     * - emphasiezed
     * - strong
     * - links
     * - images
     * - emoticons
     */
    protected function processInline($text)
    {
        while ($text) {
            if (preg_match(self::MATCH_LINK, $text, $m)) {
                return $this->processLink($m[1], $m[2], $m[3], $m[4]);
            }

            $importantTextAhead = $this->lookAhead($text, self::STRONG_START_END);
            $emphasizedTextAhead = $this->lookAhead($text, self::EMPHASIZED_START_END);

            if ($importantTextAhead === false) {
                $importantTextAhead = $this->lookAhead($text, self::STRONG_START_END_TYPE_2);
            }

            if ($emphasizedTextAhead === false) {
                $emphasizedTextAhead = $this->lookAhead($text, self::EMPHASIZED_START_END_TYPE_2);
            }

            if ($importantTextAhead !== false && $emphasizedTextAhead !== false) {
                if ($importantTextAhead <= $emphasizedTextAhead) {
                    $unformattedText = mb_substr($text, 0, $importantTextAhead);
                    $importantText = mb_substr($text, $importantTextAhead);
                    $this->text($unformattedText);

                    return $this->processStrong($importantText);
                } else {
                    $unformattedText = mb_substr($text, 0, $emphasizedTextAhead);
                    $emphasizedText = mb_substr($text, $emphasizedTextAhead);
                    $this->text($unformattedText);

                    return $this->processEmphasized($emphasizedText);
                }
            } elseif ($importantTextAhead !== false) {
                $unformattedText = mb_substr($text, 0, $importantTextAhead);
                $importantText = mb_substr($text, $importantTextAhead);
                $this->text($unformattedText);

                return $this->processStrong($importantText);
            } elseif ($emphasizedTextAhead !== false) {
                $unformattedText = mb_substr($text, 0, $emphasizedTextAhead);
                $emphasizedText = mb_substr($text, $emphasizedTextAhead);
                $this->text($unformattedText);

                return $this->processEmphasized($emphasizedText);
            }

            $end = $this->lookAhead($text, "\n");
            if ($end === false) {
                $end = mb_strlen($text);
            }
            $this->text(mb_substr($text, 0, $end));
            $text = trim(mb_substr($text, $end));
            if ($text) {
                // Add BR if text is not over
                $this->writeElement(self::NODE_BR);
            }
        };

        return ''; //All text is consumed
    }

    protected function processParagraph($text)
    {
        return $this->wrapInNode(self::NODE_PARAGRAPH, function () use ($text) {
            $end = $this->lookAhead($text, "\n\n");
            if ($end === FALSE) {
                $end = mb_strlen($text);
            }
            $this->processInLine(mb_substr($text, 0, $end));

            return trim(mb_substr($text, $end));
        });
    }

    private function processEmphasized($text)
    {
        $text = mb_substr($text, mb_strlen(self::EMPHASIZED_START_END));

        if (!$this->stateMachine['inEmphasized']) {
            $this->startElement(self::NODE_EMPHASIZED);
            $this->stateMachine['inEmphasized'] = true;
        } else {
            $this->stateMachine['inEmphasized'] = false;
            $this->endElement();
        }

        return $this->processInline($text);
    }

    private function findBlockquoteEnd($text)
    {
        $end = mb_strlen($text);
        $lastLineStartPos = mb_strrpos($text, "\n> ");
        if ($lastLineStartPos === false) {
            // one line blockquote
            $eol = mb_strpos($text, self::EOL);
        } else {
            // find end of line for the blockquote
            $eol = mb_strpos($text, self::EOL,  $lastLineStartPos + 1);
        }
        if ($eol !== false) {
            echo 3;

            return $eol;
        }

        return $end;
    }

    private function stripBlockquote($text)
    {
        $result = [];
        foreach (explode(self::EOL, $text) as $line) {
            $result[] = ltrim($line, '> ');
        }

        return implode(self::EOL, $result);
    }

    private function processBlockquote($text)
    {
        $start = 0;
        $end = $this->findBlockquoteEnd($text);
        $blockquote = $this->stripBlockquote(mb_substr($text, $start, $end));
        $this->wrapInNode(self::NODE_BLOCKQUOTE, function () use ($blockquote) {
            $this->processInline($blockquote);
        });

        return mb_substr($text, $end);
    }

    private function processLink($before, $text, $link, $after)
    {
        $title = '';

        if (preg_match('/\"(.+)\"/', $link, $m)) {
            $title = trim($m[1]);
        }

        $link = trim(preg_replace('/\"(.+)\"/', '', $link));

        $this->text($before);
        $this->startElement(self::NODE_A);
        $this->writeAttribute(self::ATTR_HREF, $link);

        if (!empty($title)) {
            $this->writeAttribute(self::ATTR_TITLE, $title);
        }

        $this->text($text);
        $this->endElement();
        $this->text($after);

        return true;
    }

    private function processStrong($text)
    {
        $text = mb_substr($text, mb_strlen(self::STRONG_START_END));

        if (!$this->stateMachine['inStrong']) {
            $this->startElement(self::NODE_STRONG);
            $this->stateMachine['inStrong'] = true;
        } else {
            $this->stateMachine['inStrong'] = false;
            $this->endElement();
        }

        return $this->processInline($text);
    }

    protected function addHeading($level, $text)
    {
        $nodeType = [
            1 => self::NODE_H1,
            2 => self::NODE_H2,
            3 => self::NODE_H3,
            4 => self::NODE_H4,
            5 => self::NODE_H5,
            6 => self::NODE_H6,
        ];
        $nodeType = $nodeType[$level];

        return $this->wrapInNode($nodeType, function () use ($text) {
            return $this->processInline($text);
        });
    }

    protected function addHorizontalRule($text)
    {
        $this->writeElement(self::NODE_HR);

        return ''; // FIXME: return remaining text
    }

    /**
     * @param \XMLReader $xml an XML document to be transformed to GitHub Markdown
     * @return String
     */
    protected function processXml($xml) {
        $output = '';

        while($xml->read()) {
            if($xml->nodeType === \XMLReader::ELEMENT && $xml->name === self::NODE_PARAGRAPH) {
                continue;
            }

            if($xml->name === self::NODE_BR) {
                $output .= "\n";
            }

            if($xml->name === self::NODE_BLOCKQUOTE && $xml->nodeType !== \XMLReader::END_ELEMENT) {
                $this->stateMachine['inBlockQuote'] = true;
            }

            if($xml->name === self::NODE_BLOCKQUOTE && $xml->nodeType === \XMLReader::END_ELEMENT) {
                $this->stateMachine['inBlockQuote'] = false;
            }

            if($xml->nodeType === \XMLReader::TEXT) {
                if($this->stateMachine['inBlockQuote']) {
                    $output .= '> ';
                }

                $output .= $xml->readString();
            }

            if($xml->nodeType === \XMLReader::END_ELEMENT && $xml->name === self::NODE_PARAGRAPH) {
                $output .= "\n\n";
            }
        }

        return trim($output);
    }
}
