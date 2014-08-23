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
            ['# H1', '<h1>H1</h1>'],
            ['## H2', '<h2>H2</h2>'],
            ['### H3', '<h3>H3</h3>'],
            ['#### H4', '<h4>H4</h4>'],
            ['##### H5', '<h5>H5</h5>'],
            ['###### H6', '<h6>H6</h6>'],
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
