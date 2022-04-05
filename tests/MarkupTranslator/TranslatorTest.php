<?php

namespace MarkupTranslator;

class TranslatorTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @covers MarkupTranslator\Translator::translate
     */
    public function testTranslate()
    {
        $testCase = 'test';
        $this->assertEquals($testCase, Translator::translate($testCase, 'github', 'github'));
    }

    /**
     * @covers MarkupTranslator\Translator::getMarkups
     */
    public function testGetMarkups()
    {
        $this->assertEquals(true, is_array(Translator::getMarkups()));
    }

}
