<?php

namespace MarkupTranslator\Translators;

abstract class Base extends \XMLWriter
{

    const NODE_ROOT = 'body';

    const NODE_PARAGRAPH = 'p';
    const NODE_BLOCKQUOTE = 'blockquote';
    const NODE_EMPHASIZED = 'em';
    const NODE_STRONG = 'strong';
    const NODE_A = 'a';
    const NODE_HR = 'hr';
    const NODE_BR = 'br';

    const NODE_H1 = 'h1';
    const NODE_H2 = 'h2';
    const NODE_H3 = 'h3';
    const NODE_H4 = 'h4';
    const NODE_H5 = 'h5';
    const NODE_H6 = 'h6';

    const ATTR_HREF = 'href';
    const ATTR_TITLE = 'title';

    abstract protected function processBlock($line);
    abstract protected function processInline($text);

    public function translate($string)
    {
        $this->openMemory();
        $this->setIndent(false);
        $this->startDocument('1.0', 'UTF-8');
        $this->startElement(self::NODE_ROOT);

        $this->processDocument($string);

        $this->endElement();
        $this->endDocument();

        return $this->outputMemory();
    }

    protected function lookAhead($haystack, $needle, $offset = 0)
    {
        return strpos($haystack, $needle, $offset);
    }

    /**
     * Loop through the whole document
     */
    protected function processDocument($text)
    {
        while ($text) {
            $text = $this->processBlock($text);
        }
    }

    /**
     * Wrap callable block in node
     */
    protected function wrapInNode($nodeType, $callback)
    {
        // Sanity check
        assert(is_string($nodeType));
        assert(is_callable($callback));

        $this->startElement($nodeType);
        $result = $callback();
        $this->endElement();

        return $result;
    }

    protected function addHeading($level, $text)
    {
        $nodeType = [
            1 => self::NODE_H1,
            2 => self::NODE_H2,
            3 => self::NODE_H3,
            4 => self::NODE_H4,
            5 => self::NODE_H5,
            6 => self::NODE_H6,
        ];

        $nodeType = $nodeType[$level];

        return $this->wrapInNode($nodeType, function() use ($text) {
            return $this->processInline($text);
        });
    }

    protected function addHorizontalRule($text)
    {
        $this->writeElement(self::NODE_HR);

        return ''; // FIXME: return remaining text
    }
    
}
