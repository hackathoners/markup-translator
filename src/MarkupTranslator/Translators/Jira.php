<?php

namespace MarkupTranslator\Translators;

class Jira extends Base
{

    const MATCH_HEADING = '/h([1-6]{1})\.\s+([^$]+)/';
    const MATCH_HR = '----';
    const BLOCKQUOTE_INLINE_START = 'bq.';
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
    }

    private function processBlockquote($text) {
        $length = strlen(self::BLOCKQUOTE_BLOCK_START);
        $text = mb_substr($text, $length); // remove first {quote}
        $end = $this->lookAhead($text, self::BLOCKQUOTE_BLOCK_START);

        if ($end === false)
        {
        // the text may include only blockqoute and no new line at the end
            $end = $this->lookAhead($text, str_replace("\n", '', self::BLOCKQUOTE_BLOCK_START));
        }

        $blockquote = mb_substr($text, 0, $end);
        $this->wrapInNode(self::NODE_BLOCKQUOTE, function() use ($blockquote) {
            $this->processInline($blockquote);
        });

        return mb_substr($text, $end + $length);
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
        }

        return ''; //All text is consumed
    }

}