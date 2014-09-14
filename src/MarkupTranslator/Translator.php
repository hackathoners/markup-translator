<?php

namespace MarkupTranslator;

class Translator
{

    public static function translate($text, $from, $to)
    {
        $markups = self::getMarkups();
        if (!isset($markups[$from])) {
            throw new \Exception(sprintf('Markup "%s" is not defined.', $from));
        }
        if (!isset($markups[$to])) {
            throw new \Exception(sprintf('Markup "%s" is not defined.', $to));
        }
        $fromClass = 'MarkupTranslator\\Translators\\' . $markups[$from];
        $fromTranslator = new $fromClass();
        $toClass = 'MarkupTranslator\\Translators\\' . $markups[$to];
        $toTranslator = new $toClass();

        return $toTranslator->xmlToText($fromTranslator->translate($text));
    }

    public static function getMarkups()
    {
        return [
            'github' => 'Github',
            'jira' => 'Jira'
        ];
    }
}
