<?php

namespace MarkupTranslator\Translators;

abstract class Base extends \XMLWriter
{

    const NODE_ROOT = 'body';

    const NODE_PARAGRAPH = 'p';
    const NODE_HR = 'hr';

    const NODE_H1 = 'h1';
    const NODE_H2 = 'h2';
    const NODE_H3 = 'h3';
    const NODE_H4 = 'h4';
    const NODE_H5 = 'h5';
    const NODE_H6 = 'h6';

    abstract protected function processLine($line);

    private function init() {
        $this->openMemory();
        $this->setIndent(false);
        $this->startDocument('1.0', 'UTF-8');
    }

    public function translate($string)
    {
        $this->init();
        $this->startElement(self::NODE_ROOT);
        $lines = explode("\n", $string);
        foreach($lines as $line)
        {
            $this->processLine(rtrim($line));
        }
        $this->endElement();
        $this->endDocument();
        return $this->outputMemory();
    }
}
