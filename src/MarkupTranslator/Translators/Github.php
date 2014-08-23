<?php

namespace MarkupTranslator\Translators;

class Github extends Base
{

    const MATCH_HEADING = '/([#]{1,6})\s+([^$]+)/';

    protected function processLine($line)
    {
        if (in_array(substr($line, 0, 3), ['---', '***', '___']))
        {
            return $this->addHorizontalRule();
        }
        if (preg_match(self::MATCH_HEADING, $line, $m)) {
            return $this->addHeading(strlen($m[1]), $m[2]);
        }
        $this->addParagraph($line);
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
