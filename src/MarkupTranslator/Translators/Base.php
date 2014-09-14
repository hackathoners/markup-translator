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
    abstract protected function processInline($text);
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

    protected function processParagraph($text)
    {
        return $this->wrapInNode(self::NODE_PARAGRAPH, function () use ($text) {
            $end = $this->lookAhead($text, "\n\n");
            if ($end === FALSE) {
                $end = mb_strlen($text);
            }
            $this->processInLine(mb_substr($text, 0, $end));

            return trim(mb_substr($text, $end));
        });
    }

    protected function processRestOfLine($text) {
        $end = $this->lookAhead($text, "\n");
        if ($end === false) {
            $end = mb_strlen($text);
        }

        $this->text(mb_substr($text, 0, $end));
        $text = trim(mb_substr($text, $end));

        if ($text) {
            // Add BR if text is not over
            $this->writeElement(self::NODE_BR);
        }

        return $text;
    }

    public function xmlToText($source) {
        $source = trim($source);

        if(empty($source))
        {
            throw new \Exception('Empty source');
        }

        $xml = new \XMLReader();
        $xml->XML($source, self::DEFAULT_ENCODING);

        return $this->processXml($xml);
    }
}
