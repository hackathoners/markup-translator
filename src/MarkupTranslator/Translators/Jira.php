<?php

namespace MarkupTranslator\Translators;

class Jira extends Base
{

    const MATCH_HEADING = '/h([1-6]{1})\.\s+([^$]+)/';
    const MATCH_HR = '----';
    const BLOCKQUOTE_INLINE_START = 'bq. ';
    const BLOCKQUOTE_BLOCK_START = "{quote}\n";

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

    private function processBlockquote($text, $inline = false) {
        if( $inline ) {
            $length = 0;
            $text = str_replace(self::BLOCKQUOTE_INLINE_START, '', $text); // remove bq.
        } else {
            $length = strlen(self::BLOCKQUOTE_BLOCK_START);
            $text = mb_substr($text, $length); // remove first {quote}
        }

        $end = $this->findBlockquoteEnd($text, $inline);
        $blockquote = mb_substr($text, 0, $end);
        $this->wrapInNode(self::NODE_BLOCKQUOTE, function() use ($blockquote) {
            $this->processInline($blockquote);
        });

        return mb_substr($text, $end + $length);
    }

    private function findBlockquoteEnd($text, $inline = false)
    {
        $end = $this->lookAhead($text, ($inline ? "\n" : self::BLOCKQUOTE_BLOCK_START));

        if ($end === false && !$inline)
        {
            // the text may include only blockqoute and no new line at the end
            $end = $this->lookAhead($text, str_replace("\n", '', self::BLOCKQUOTE_BLOCK_START));
        }

        if ($end === false)
        {
            $end = mb_strlen($text);
        }

        return $end;
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
        while($text) {
            $text = $this->processRestOfLine($text);
        }

        return ''; //All text is consumed
    }

}