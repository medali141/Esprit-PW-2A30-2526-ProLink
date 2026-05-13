<?php
declare(strict_types=1);
// Test script to check if PHP sees OPENAI_API_KEY in various places.
// WARNING: This script never prints the key value itself, only whether it's set.
header('Content-Type: text/plain; charset=utf-8');

$g = getenv('OPENAI_API_KEY');
$e = array_key_exists('OPENAI_API_KEY', $_ENV) ? $_ENV['OPENAI_API_KEY'] : null;
$s = array_key_exists('OPENAI_API_KEY', $_SERVER) ? $_SERVER['OPENAI_API_KEY'] : null;
echo "getenv('OPENAI_API_KEY'): " . ($g ? 'SET (len=' . strlen($g) . ')' : 'NOT_SET') . PHP_EOL;
echo "_ENV[OPENAI_API_KEY]: " . ($e ? 'SET (len=' . strlen($e) . ')' : 'NOT_SET') . PHP_EOL;
echo "_SERVER[OPENAI_API_KEY]: " . ($s ? 'SET (len=' . strlen($s) . ')' : 'NOT_SET') . PHP_EOL;

echo PHP_EOL . "Pour tester la modération : soumettez un message depuis l'interface du forum." . PHP_EOL;