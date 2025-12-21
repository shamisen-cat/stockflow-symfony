<?php

declare(strict_types=1);

namespace App\Domain\Shared\Exception\Trait;

/**
 * 例外メッセージの一部を短縮するためのトレイト
 */
trait TruncateTrait
{
    /** 短縮の基準となる文字数 */
    private const int TRUNCATE_LENGTH = 20;
    /** 短縮時に付与する文字列 */
    private const string TRUNCATE_SUFFIX = '...';

    /**
     * 文字列を規定の長さに短縮する。
     *
     * @param string $value 文字列
     *
     * @return string 短縮後の文字列、または元の文字列
     */
    private static function truncate(string $value): string
    {
        if (mb_strlen($value, 'UTF-8') <= self::TRUNCATE_LENGTH) {
            return $value;
        }

        return mb_substr($value, 0, self::TRUNCATE_LENGTH, 'UTF-8').self::TRUNCATE_SUFFIX;
    }
}
