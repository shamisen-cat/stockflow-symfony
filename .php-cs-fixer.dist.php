<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    // 対象ディレクトリー
    ->in([
        __DIR__.'/src',
        __DIR__.'/config',
        __DIR__.'/public',
        __DIR__.'/tests',
    ])

    // 除外ディレクトリー
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
        '@PSR12'   => true,
        '@Symfony' => true,

        // 型宣言
        'declare_strict_types' => true,
        'strict_param'         => true,

        // クラス・メソッド
        'class_attributes_separation' => [
            'elements' => [
                'method'   => 'one',
                'property' => 'one',
            ],
        ],
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],

        // インポート
        'no_unused_imports' => true,
        'ordered_imports'   => [
            'sort_algorithm' => 'alpha',
        ],

        // 制御構文
        'no_useless_else'   => true,
        'no_useless_return' => true,
        'yoda_style'        => false,

        // 文字列・配列
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'single_quote'                => true,
        'trailing_comma_in_multiline' => [
            'elements' => [
                'arguments',
                'arrays',
                'parameters',
            ],
        ],

        // スペース
        'binary_operator_spaces' => [
            'default' => 'align_single_space_minimal',
        ],
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_trailing_whitespace'                      => true,

        // 改行・空行
        'line_ending'          => true,
        'no_extra_blank_lines' => [
            'tokens' => ['extra'],
        ],
        'single_blank_line_at_eof' => true,
        'single_line_throw'        => false,

        // PHPDoc
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
        'phpdoc_order'   => true,
        'phpdoc_summary' => false,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)  // @Symfonyに該当ルールが含まれているため
;
