<?php

namespace MarkupTranslator;

class TranslatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers MarkupTranslator\Translator::translate
     */
    public function testTranslate()
    {
        $testCase = 'test';
        $this->assertEquals($testCase, Translator::translate($testCase, '', ''));
    }


    /**
     * @covers MarkupTranslator\Translator::getMarkups
     */
    public function testGetMarkups()
    {
        $this->assertEquals(true, is_array(Translator::getMarkups()));
    }

}

