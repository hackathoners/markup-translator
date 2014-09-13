<?php

namespace MarkupTranslator\Translators;


class TestsBase extends \PHPUnit_Framework_TestCase
{

    protected function cleanXML($text) {
        $replace = [
            '<?xml version="1.0" encoding="UTF-8"?>' => '',
            "\n" => '',
            '<body>' => '',
            '</body>' => '',
            '<body/>' => '',
        ];
        return str_replace(array_keys($replace), $replace, $text);
    }

}
