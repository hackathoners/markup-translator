<?php

namespace MarkupTranslator\Translators;

class GithubTest extends \PHPUnit_Framework_TestCase
{

    private function cleanXML($text) {
        $replace = [
            '<?xml version="1.0" encoding="UTF-8"?>' => '',
            "\n" => '',
            '<body>' => '',
            '</body>' => '',
        ];
        return str_replace(array_keys($replace), $replace, $text);
    }

    public function translateProvider() 
    {
        return [
            ['test', '<p>test</p>'],
            ['---', '<hr/>'],
            ['----', '<hr/>'],
            ['****', '<hr/>'],
            ['_____', '<hr/>'],
        ];
    }

    /**
     * @covers MarkupTranslator\Translators\Github::translate
     * @dataProvider translateProvider
     */
    public function testTranslate($text, $expected)
    {
        $translator = new Github();
        $this->assertEquals(
            $expected,
            $this->cleanXml($translator->translate($text))
        );
    }
}
