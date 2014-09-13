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

    const DEFAULT_ENCODING = 'UTF-8';

    abstract protected function getMarkupName();
    abstract protected function processBlock($line);
    abstract protected function processXml($xml);

    public function translate($string)
    {
        $this->openMemory();
        $this->setIndent(false);
        $this->startDocument('1.0', self::DEFAULT_ENCODING);
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

    public function xmlToText($source) {
        $xml = new \XMLReader();
        $xml->XML($source, self::DEFAULT_ENCODING);

        if ( !$xml->isValid()) {
            throw new \Exception('Invalid XML source');
        }

        return $this->processXml($xml);
    }
}
