<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfig;

$finder = new Finder()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->exclude(__DIR__ . '/vendor')
;

$rules = [
    '@PHP8x4Migration' => true,
    '@PhpCsFixer' => true,
    'declare_strict_types' => true,
    'global_namespace_import' => true,
    'concat_space' => false,
    'method_argument_space' => false,
    'single_line_throw' => false,
    'php_unit_test_class_requires_covers' => false,
    'types_spaces' => ['space_multiple_catch' => 'single'],
    'phpdoc_order' => ['order' => ['param', 'throws', 'return']],
];

return new Config()
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache')
    ->setFinder($finder)
    ->setRules($rules)
    ->setRiskyAllowed(true)
    ->setParallelConfig(new ParallelConfig(4))
    ->setLineEnding("\n")
    ->setUsingCache(false)
    ->setUnsupportedPhpVersionAllowed(true)
;
