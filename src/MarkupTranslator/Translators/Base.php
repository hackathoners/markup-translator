<?php

namespace MarkupTranslator\Translators;

abstract class Base extends \XMLWriter
{

    const NODE_ROOT = 'body';

    const NODE_PARAGRAPH = 'p';
    const NODE_HR = 'hr';

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
            $this->processLine(trim($line));
        }
        $this->endElement();
        $this->endDocument();
        return $this->outputMemory();
    }
}
