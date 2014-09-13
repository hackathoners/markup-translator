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
            ['> test', '<blockquote>test</blockquote>'],
            ["> test1\n> test2", '<blockquote>test1<br/>test2</blockquote>'],
            ['*This text will be emphasized*', '<p><em>This text will be emphasized</em></p>'],
            ['**This text will be bold**', '<p><strong>This text will be bold</strong></p>'],
            ['This text will not be emphasized. *But this will be.*', '<p>This text will not be emphasized. <em>But this will be.</em></p>'],
            ['This text will not be bold. **But this will be.**', '<p>This text will not be bold. <strong>But this will be.</strong></p>'],
            ['This text will not be emphasized. *But this will be.* And this again not emphasized.', '<p>This text will not be emphasized. <em>But this will be.</em> And this again not emphasized.</p>'],
            ['This text will not be bold. **But this will be.** And this again without bold.', '<p>This text will not be bold. <strong>But this will be.</strong> And this again without bold.</p>'],
            ['**Everyone *must* attend the meeting at 5 today.**', '<p><strong>Everyone <em>must</em> attend the meeting at 5 today.</strong></p>'],
            ['_This text will be emphasized_', '<p><em>This text will be emphasized</em></p>'],
            ['__This text will be bold__', '<p><strong>This text will be bold</strong></p>'],
            ['This text will not be emphasized. _But this will be._', '<p>This text will not be emphasized. <em>But this will be.</em></p>'],
            ['This text will not be bold. __But this will be.__', '<p>This text will not be bold. <strong>But this will be.</strong></p>'],
            ['This text will not be emphasized. _But this will be._ And this again not emphasized.', '<p>This text will not be emphasized. <em>But this will be.</em> And this again not emphasized.</p>'],
            ['This text will not be bold. __But this will be.__ And this again without bold.', '<p>This text will not be bold. <strong>But this will be.</strong> And this again without bold.</p>'],
            ['__Everyone _must_ attend the meeting at 5 today.__', '<p><strong>Everyone <em>must</em> attend the meeting at 5 today.</strong></p>'],
#            ['**Everyone _must_ attend the meeting at 5 today.**', '<p><strong>Everyone <em>must</em> attend the meeting at 5 today.</strong></p>'],
            ['***', '<hr/>'],
            ['* * *', '<hr/>'],
            ['* * * *', '<hr/>'],
            ['*****', '<hr/>'],
            ['---', '<hr/>'],
            ['- - -', '<hr/>'],
            ['----', '<hr/>'],
            ['---------------------------------------', '<hr/>'],
            ['# H1', '<h1>H1</h1>'],
            ['## H2', '<h2>H2</h2>'],
            ['### H3', '<h3>H3</h3>'],
            ['#### H4', '<h4>H4</h4>'],
            ['##### H5', '<h5>H5</h5>'],
            ['###### H6', '<h6>H6</h6>'],
            ['This is an [example link](http://example.com/).', '<p>This is an <a href="http://example.com/">example link</a>.</p>'],
            ['This is an [example link](http://example.com/ "With a Title").', '<p>This is an <a href="http://example.com/" title="With a Title">example link</a>.</p>'],
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

    /**
     * @covers MarkupTranslator\Translators\Github::xmlToText
     */
    public function testXmlToText_throws_exception()
    {
        $translator = new Github();
        $this->setExpectedException('Exception', 'Empty source');
        $translator->xmlToText('            ');
    }

    /**
     * @dataProvider xmlToTextProvider
     */
    public function testXmlToText($xml, $expected)
    {
        $translator = new Github();
        $this->assertEquals(
            $expected,
            $translator->xmlToText($xml)
        );
    }

    public function xmlToTextProvider() {
        return [
            ['<root><p>test</p></root>', 'test'],
            ['<root><p>test1</p><p>test2</p></root>', "test1\n\ntest2"],
            ['<root><p>test1<br/>test2</p></root>', "test1\ntest2"],
            ['<root><blockquote>test</blockquote></root>', '> test'],
            ['<root><blockquote>test1<br/>test2</blockquote></root>', "> test1\n> test2"],
            ['<root><p><em>This text will be emphasized</em></p></root>', '*This text will be emphasized*'],
            ['<root><p><strong>This text will be bold</strong></p></root>', '**This text will be bold**'],
            ['<root><p>This text will not be emphasized. <em>But this will be.</em></p></root>', 'This text will not be emphasized. *But this will be.*'],
            ['<root><p>This text will not be bold. <strong>But this will be.</strong></p></root>', 'This text will not be bold. **But this will be.**'],
            ['<root><p>This text will not be emphasized. <em>But this will be.</em> And this again not emphasized.</p></root>', 'This text will not be emphasized. *But this will be.* And this again not emphasized.'],
            ['<root><p>This text will not be bold. <strong>But this will be.</strong> And this again without bold.</p></root>', 'This text will not be bold. **But this will be.** And this again without bold.'],
            ['<root><p><strong>Everyone <em>must</em> attend the meeting at 5 today.</strong></p></root>', '**Everyone *must* attend the meeting at 5 today.**'],
            ['<hr/>', '***'],
            /*
            ['<h1>H1</h1>', '# H1'],
            ['<h2>H2</h2>', '## H2'],
            ['<h3>H3</h3>', '### H3'],
            ['<h4>H4</h4>', '#### H4'],
            ['<h5>H5</h5>', '##### H5'],
            ['<h6>H6</h6>', '###### H6'],
            ['<p>This is an <a href="http://example.com/">example link</a>.</p>', 'This is an [example link](http://example.com/).'],
            ['<p>This is an <a href="http://example.com/" title="With a Title">example link</a>.</p>', 'This is an [example link](http://example.com/ "With a Title").'],
            */
        ];
    }
}
