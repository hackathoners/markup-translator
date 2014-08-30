<?php

namespace MarkupTranslator\Translators;

class Github extends Base
{

    const BLOCKQUOTE_START = '> ';
    const MATCH_HEADING = '/([#]{1,6})\s+([^$]+)/';

    protected $stateMachine = [
        'inBlockQuote' => false
    ];

    protected function processDocument($text)
    {
        while ($text)
        {
            $text = $this->processBlock($text);
        }
        /*
        if (in_array(substr($line, 0, 3), ['---', '***', '___']))
        {
            return $this->addHorizontalRule();
        }
        if (preg_match(self::MATCH_HEADING, $line, $m)) {
            return $this->addHeading(strlen($m[1]), $m[2]);
        }
        $this->addParagraph($line);
        */
    }

    private function processBlock($text)
    {
        $this->startElement(self::NODE_PARAGRAPH);

        $end = $this->lookAhead($text, "\n\n");
        if ($end === FALSE)
        {
            $end = mb_strlen($text);
        }
        $this->processInline(mb_substr($text, 0, $end));
        $this->endElement();
        return trim(mb_substr($text, $end));
    }

    protected function processInline($text)
    {
        while ($text)
        {
            if (mb_substr($text, 0, 2) === self::BLOCKQUOTE_START)
            {
                return $this->processBlockquote($text);
            }
            $end = $this->lookAhead($text, "\n");
            if ($end === false)
            {
                $end = mb_strlen($text);
            }
            $this->text(mb_substr($text, 0, $end));
            $text = trim(mb_substr($text, $end));
            if ($text)
            {
                // Add BR if text is not over
                $this->writeElement(self::NODE_BR);
            }
        };
    }

    private function processBlockquote($text)
    {
        $text = mb_substr($text, mb_strlen(self::BLOCKQUOTE_START));
        if(!$this->stateMachine['inBlockQuote'])
        {
            $this->startElement(self::NODE_BLOCKQUOTE);
            $this->stateMachine['inBlockQuote'] = true;

            $this->processInline($text);

            $this->endElement();
            $this->stateMachine['inBlockQuote'] = false;
        } else {
            $this->processInline($text);
        }
    }

    protected function addHeading($level, $text) {
        $nodeType = self::NODE_H6;
        $types = [
            1 => self::NODE_H1,
            2 => self::NODE_H2,
            3 => self::NODE_H3,
            4 => self::NODE_H4,
            5 => self::NODE_H5,
            6 => self::NODE_H6,
        ];
        if (isset($types[$level])) {
            $nodeType = $types[$level];
        };
        return $this->writeElement($nodeType, $text);
    }
}
