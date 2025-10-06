<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    // 対象ディレクトリ
    ->in([
        __DIR__.'/src',
        __DIR__.'/config',
        __DIR__.'/public',
        __DIR__.'/tests',
    ])

    // 除外ディレクトリ
    ->exclude([
        'var',
        'vendor',
        'bin',
        'migrations',
    ])

    // 対象ファイル
    ->name('*.php')
    ->notName('*.blade.php')
    ->notName('*.twig')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
;

return (new Config())
    ->setRules([
        // 基本ルール
        '@Symfony'             => true,
        '@PSR12'               => true,
        'declare_strict_types' => true,
        'strict_param'         => true,

        // インポート
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
        ],
        'no_unused_imports' => true,

        // 条件式
        'yoda_style'        => false,
        'no_useless_else'   => true,
        'no_useless_return' => true,

        // 改行
        'line_ending'              => true,
        'single_blank_line_at_eof' => true,
        'no_extra_blank_lines'     => [
            'tokens' => ['extra'],
        ],
        'single_line_throw' => false,

        // スペース
        'binary_operator_spaces' => [
            'default' => 'align_single_space_minimal',
        ],
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_trailing_whitespace'                      => true,

        // 文字列/配列
        'single_quote' => true,
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'trailing_comma_in_multiline' => [
            'elements' => [
                'arrays',
                'arguments',
                'parameters',
            ],
        ],

        // クラス/メソッド
        'class_attributes_separation' => [
            'elements' => [
                'property' => 'one',
                'method'   => 'one',
            ],
        ],
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],

        // PHPDoc
        'phpdoc_summary' => false,
        'phpdoc_order'   => true,
        'phpdoc_align'   => [
            'align' => 'vertical',
            'tags'  => [
                'param',
                'return',
                'throws',
                'var',
                'type',
            ],
        ],
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)  // @Symfonyに該当ルールが含まれているため
;
