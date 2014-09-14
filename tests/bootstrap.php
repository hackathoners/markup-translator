<?php

$loader = require __DIR__ . "/../vendor/autoload.php";

function cleanXML($text)
{
    $replace = [
        '<?xml version="1.0" encoding="UTF-8"?>' => '',
        "\n" => '',
        '<body>' => '',
        '</body>' => '',
        '<body/>' => '',
    ];

    return str_replace(array_keys($replace), $replace, $text);
}

function decorateWithRootNode($xmlString)
{
    return '<root>' . $xmlString . '</root>';
}
