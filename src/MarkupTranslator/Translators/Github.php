<?php

namespace MarkupTranslator\Translators;

class Github extends Base
{

    

    protected function processLine($line)
    {
        if (in_array($line, ['---', '***', '___'])) 
        {
            return $this->addHorizontalRule();
        }
        $this->addParagraph($line);
    }


    protected function addParagraph($string)
    {
        return $this->writeElement(self::NODE_PARAGRAPH, $string);
    }
    
    protected function addHorizontalRule()
    {
        return $this->writeElement(self::NODE_HR);    
    }
}
