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

    /**
     * @covers MarkupTranslator\Translators\Github::translate
     */
    public function testTranslate()
    {
        $testCases = [
            [
                'text' =>  'test',
                'expected' => '<p>test</p>',
            ],
            [
                'text' => '---',
                'expected' => '<hr/>',
            ]
        ];
        $translator = new Github();
        foreach ($testCases as $testCase)
        {
            $this->assertEquals(
                $testCase['expected'],
                $this->cleanXml($translator->translate($testCase['text']))
            );
        }
    }
}
