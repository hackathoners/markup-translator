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
        // https://help.github.com/articles/markdown-basics
        return [
            ['test', '<p>test</p>'],
            ["test1\n\ntest2", '<p>test1</p><p>test2</p>'],
            ["test1\ntest2", '<p>test1<br/>test2</p>'],
/*
            ['> test', '<p><blockquote>test</blockquote></p>'],
            ['*This text will be italic*', '<p><em>This text will be italic</em></p>'],
            ['**This text will be bold**', '<p><strong>This text will be bold</strong></p>'],
            ['**Everyone _must_ attend the meeting at 5 today.**', '<p><strong>Everyone <em>must</em> attend the meeting at 5 clock today.</strong</p>'],
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
*/
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
