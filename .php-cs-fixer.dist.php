<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->name([
        '*.php',
    ]);

return (new Config())
    ->setRules([
        '@Symfony' => true,

        'declare_strict_types' => true,
        'strict_param'         => true,
        'no_useless_else'      => true,
        'no_useless_return'    => true,
        'yoda_style'           => false,

        'array_syntax' => [
            'syntax' => 'short',
        ],

        'single_quote'             => true,
        'line_ending'              => true,
        'single_blank_line_at_eof' => true,
        'no_trailing_whitespace'   => true,

        'trailing_comma_in_multiline' => [
            'elements' => [
                'arguments',
                'arrays',
                'parameters',
            ],
        ],

        'class_attributes_separation' => [
            'elements' => [
                'method'   => 'one',
                'property' => 'one',
            ],
        ],

        'binary_operator_spaces' => [
            'default' => 'align_single_space_minimal',
        ],

        'no_multiline_whitespace_around_double_arrow' => true,

        'no_extra_blank_lines' => [
            'tokens' => ['extra'],
        ],

        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],

        'single_line_throw' => false,

        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
        ],

        'no_unused_imports' => true,

        'phpdoc_order'      => true,
        'phpdoc_summary'    => false,
        'phpdoc_to_comment' => false,

        'phpdoc_align' => [
            'align' => 'vertical',
            'tags'  => [
                'param',
                'return',
                'throws',
                'type',
                'var',
            ],
        ],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder);
