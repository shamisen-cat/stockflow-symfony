<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->name('*.php');

return (new Config())
    // Based on Symfony defaults, then override where project conventions differ.
    ->setRules([
        '@Symfony' => true,

        // Prefer strict_types declarations and strict comparison parameters.
        'declare_strict_types' => true,
        'strict_param' => true,

        // Keep natural (non-Yoda) comparisons for readability.
        'yoda_style' => false,

        // Allow multi-line throw expressions when clearer.
        'single_line_throw' => false,

        // Enforce fully multiline argument lists; also require trailing commas
        // on call arguments (in addition to Symfony defaults).
        'method_argument_space' => [
            'after_heredoc' => true,
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'trailing_comma_in_multiline' => [
            'after_heredoc' => true,
            'elements' => [
                'arguments',
                'array_destructuring',
                'arrays',
                'match',
                'parameters',
            ],
        ],

        // Leave PHPDoc structure and wording to authors.
        'phpdoc_to_comment' => false,
        'phpdoc_summary' => false,
        'phpdoc_align' => false,
    ])
    // Required for risky rules such as declare_strict_types and strict_param.
    ->setRiskyAllowed(true)
    ->setFinder($finder);
