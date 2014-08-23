<?php

namespace MarkupTranslator\Translators;

class GithubTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers MarkupTranslator\Translators\Github::translate
     */
    public function testTranslate()
    {
        $testCase = 'test';
        $expected = '<?xml version="1.0" encoding="UTF-8"?>
<body><p>test</p></body>
';
        $translator = new Github();
        $this->assertEquals(
            $expected,
            $translator->translate($testCase)
        );
    }
}
