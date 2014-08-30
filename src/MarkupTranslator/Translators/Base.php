<?php

namespace MarkupTranslator\Translators;

abstract class Base extends \XMLWriter
{

    const NODE_ROOT = 'body';

    const NODE_PARAGRAPH = 'p';
    const NODE_BLOCKQUOTE = 'blockquote';
    const NODE_HR = 'hr';
    const NODE_BR = 'br';

    const NODE_H1 = 'h1';
    const NODE_H2 = 'h2';
    const NODE_H3 = 'h3';
    const NODE_H4 = 'h4';
    const NODE_H5 = 'h5';
    const NODE_H6 = 'h6';

    abstract protected function processDocument($line);

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
}
