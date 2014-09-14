<?php

namespace MarkupTranslator\Translators;

class JiraTest extends \PHPUnit_Framework_TestCase
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
            ['bq. test', '<blockquote>test</blockquote>'],
            ["{quote}\ntest\n{quote}", '<blockquote>test</blockquote>'],
            ["{quote}\ntest1\ntest2{quote}", '<blockquote>test1<br/>test2</blockquote>'],
            ['test', '<p>test</p>'],
            ["test1\n\ntest2", '<p>test1</p><p>test2</p>'],
            ["test1\ntest2", '<p>test1<br/>test2</p>'],
            ['_This text will be emphasized_', '<p><em>This text will be emphasized</em></p>'],
            ['This text will not be emphasized. _But this will be._', '<p>This text will not be emphasized. <em>But this will be.</em></p>'],
            ['This text will not be emphasized. _But this will be._ And this again not emphasized.', '<p>This text will not be emphasized. <em>But this will be.</em> And this again not emphasized.</p>'],
            ['*This text will be bold*', '<p><strong>This text will be bold</strong></p>'],
            ['This text will not be bold. *But this will be.*', '<p>This text will not be bold. <strong>But this will be.</strong></p>'],
            ['This text will not be bold. *But this will be.* And this again without bold.', '<p>This text will not be bold. <strong>But this will be.</strong> And this again without bold.</p>'],
            ['*Everyone _must_ attend the meeting at 5 today.*', '<p><strong>Everyone <em>must</em> attend the meeting at 5 today.</strong></p>'],
#           ['_Everyone *must* attend the meeting at 5 today._', '<p><strong>Everyone <em>must</em> attend the meeting at 5 today.</strong></p>'],
            ['This is an example link: http://example.com/.', '<p>This is an example link: <a href="http://example.com/">http://example.com/</a>.</p>'],
            ['This is an [example link|http://example.com/].', '<p>This is an <a href="http://example.com/">example link</a>.</p>'],
            ['This is an [example link|http://example.com/]. And this is an [example link|http://example.com/].', '<p>This is an <a href="http://example.com/">example link</a>. And this is an <a href="http://example.com/">example link</a>.</p>'],
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
            cleanXml($translator->translate($text))
        );
    }

}
