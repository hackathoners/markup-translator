<?php

namespace MarkupTranslator\Translators;

class Jira extends Base
{

    const MATCH_HEADING = '/h([1-6]{1})\.\s+([^$]+)/';
    const MATCH_HR = '----';
    const BLOCKQUOTE_INLINE_START = 'bq. ';
    const BLOCKQUOTE_BLOCK_START = "{quote}\n";
    const EMPHASIZED_START_END = '_';
    const STRONG_START_END = '*';

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

    private function processEmphasized($text) {
        $emphasizedTextBegin = $this->lookAhead($text, self::EMPHASIZED_START_END);
        $beforeEmphasizedText = mb_substr($text, 0, $emphasizedTextBegin);
        $emphasizedTextEnd = $this->lookAhead(mb_substr($text, $emphasizedTextBegin + 1), self::EMPHASIZED_START_END);
        $afterEmphasizedText = mb_substr(mb_substr($text, $emphasizedTextBegin + 1), $emphasizedTextEnd + 1);
        $emphasizedText = mb_substr($text, $emphasizedTextBegin + 1, $emphasizedTextEnd);

        if( $beforeEmphasizedText !== '' ) {
            $this->processInline($beforeEmphasizedText);
        }

        $this->wrapInNode(self::NODE_EMPHASIZED, function() use ($emphasizedText) {
            $this->processInline($emphasizedText);
        });

        if( $afterEmphasizedText !== '' ) {
            $this->processInline($afterEmphasizedText);
        }

        return '';
    }

    private function processStrong($text) {
        $strongTextBegin = $this->lookAhead($text, self::STRONG_START_END);
        $beforeStrongText = mb_substr($text, 0, $strongTextBegin);
        $strongTextEnd = $this->lookAhead(mb_substr($text, $strongTextBegin + 1), self::STRONG_START_END);
        $afterStrongText = mb_substr(mb_substr($text, $strongTextBegin + 1), $strongTextEnd + 1);
        $strongText = mb_substr($text, $strongTextBegin + 1, $strongTextEnd);

        if( $beforeStrongText !== '' ) {
            $this->processInline($beforeStrongText);
        }

        $this->wrapInNode(self::NODE_STRONG, function() use ($strongText) {
            $this->processInline($strongText);
        });

        if( $afterStrongText !== '' ) {
            $this->processInline($afterStrongText);
        }

        return '';
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
            if( $this->lookAhead($text, self::EMPHASIZED_START_END) !== false ) {
                return $this->processEmphasized($text);
            }

            if( $this->lookAhead($text, self::STRONG_START_END) !== false ) {
                return $this->processStrong($text);
            }

            $text = $this->processRestOfLine($text);
        }

        return ''; //All text is consumed
    }

}