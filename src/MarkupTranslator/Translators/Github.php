<?php

namespace MarkupTranslator\Translators;

class Github extends Base
{

    const MATCH_HEADING = '/([#]{1,6})\s+([^$]+)/';

    protected function processDocument($string)
    {
        while ($string) {
            $string = $this->processParagraph($string);
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

    private function processParagraph($string) {
        $this->startElement(self::NODE_PARAGRAPH);

        $end = $this->lookAhead($string, "\n\n");
        if ($end === FALSE)
        {
            $end = mb_strlen($string);
        }
        // FIXME: search for elements here
        $this->text(mb_substr($string, 0, $end));

        $this->endElement();
        return trim(mb_substr($string, $end));
    }

    protected function processInline($line)
    {
        $replaces = [
            '/\*{1}([^*])\*{1}/' => self::NODE_EM + '$1' + self::NODE_EM
        ];
        return preg_replace(array_keys($replaces), $replaces, $line);
    }

    protected function addParagraph($text)
    {
        return $this->writeElement(self::NODE_PARAGRAPH, $text);
    }

    protected function addHorizontalRule()
    {
        return $this->writeElement(self::NODE_HR);
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
