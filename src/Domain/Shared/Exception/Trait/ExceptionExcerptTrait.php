<?php

declare(strict_types=1);

namespace App\Domain\Shared\Exception\Trait;

/**
 * 例外メッセージで使用する値の短縮表示を生成するトレイト
 */
trait ExceptionExcerptTrait
{
    /** 短縮表示の最大文字数 */
    private const EXCERPT_LENGTH = 20;

    /** 省略記号 */
    private const EXCERPT_ELLIPSIS = '...';

    /**
     * 値の短縮表示を取得する。
     *
     * @param string $value 例外メッセージで使用する値
     *
     * @return string 値の短縮表示
     */
    private static function getExcerpt(string $value): string
    {
        return mb_strlen($value) > self::EXCERPT_LENGTH
            ? mb_substr($value, 0, self::EXCERPT_LENGTH).self::EXCERPT_ELLIPSIS
            : $value;
    }
}
