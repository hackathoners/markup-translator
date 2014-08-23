<?php

namespace MarkupTranslator\Translators;

class Github extends Base
{

    protected function processLine($line)
    {
        $this->addParagraph($line);
    }


    protected function addParagraph($string)
    {
        $this->setElement(self::NODE_PARAGRAPH, $string);
    }
}
