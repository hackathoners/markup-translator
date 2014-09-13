<?php

namespace MarkupTranslator\Translators;

class JiraTest extends TestsBase
{
    public function translateProvider()
    {
        // https://jira.atlassian.com/secure/WikiRendererHelpAction.jspa?section=all
        return [
            ['', ''],
        ];
    }

    /**
     * @covers MarkupTranslator\Translators\Jira::translate
     * @dataProvider translateProvider
     */
    public function testTranslate($text, $expected)
    {
        $translator = new Jira();
        $this->assertEquals(
            $expected,
            $this->cleanXml($translator->translate($text))
        );
    }

}
