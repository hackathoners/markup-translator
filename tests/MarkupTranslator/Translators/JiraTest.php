<?php

namespace MarkupTranslator\Translators;

class JiraTest extends TestsBase
{
    public function translateProvider()
    {
        // https://jira.atlassian.com/secure/WikiRendererHelpAction.jspa?section=all
        return [
            ['', ''],
            ['h1. H1', '<h1>H1</h1>'],
            ['h2. H2', '<h2>H2</h2>'],
            ['h3. H3', '<h3>H3</h3>'],
            ['h4. H4', '<h4>H4</h4>'],
            ['h5. H5', '<h5>H5</h5>'],
            ['h6. H6', '<h6>H6</h6>'],
            ['h6. H6', '<h6>H6</h6>'],
            ['----', '<hr/>'],
#            ['bq. test', '<blockquote>test</blockquote>'],
            ["{quote}\ntest\n{quote}", '<blockquote>test</blockquote>'],
            ["{quote}\ntest1\ntest2{quote}", '<blockquote>test1<br/>test2</blockquote>'],
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
