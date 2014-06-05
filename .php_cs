<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');
;

$fixers = [
    'trailing_spaces',
    'linefeed',
    'short_tag',
    'indentation',
    'eof_ending',
    'unused_use',
    'php_closing_tag',
    'extra_empty_lines',
    'controls_spaces',
    'braces',
    'visibility'
];

return Symfony\CS\Config\Config::create()
    ->fixers($fixers)
    ->finder($finder)
;
