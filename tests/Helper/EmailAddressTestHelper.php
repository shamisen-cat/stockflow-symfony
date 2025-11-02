<?php

declare(strict_types=1);

namespace App\Tests\Helper;

/**
 * メールアドレスのテストヘルパークラス
 */
final class EmailAddressTestHelper
{
    // --- テストデータ ---

    /** 標準のメールアドレス */
    public const TEST_VALUE = 'test@example.com';

    /** TEST_VALUEと異なるメールアドレス */
    public const DIFFERENT_VALUE = 'different@example.com';

    /**
     * 有効な最大文字数のメールアドレス
     */
    public static function validMaxLengthValue(): string
    {
        return str_repeat('x', 64).'@'.
               str_repeat('x', 63).'.'.
               str_repeat('x', 63).'.'.
               str_repeat('x', 61);
    }

    /**
     * データベースの最大文字数を超える境界値のメールアドレス
     */
    public static function tooLongValue(): string
    {
        return str_repeat('x', 64).'@'.
               str_repeat('x', 63).'.'.
               str_repeat('x', 63).'.'.
               str_repeat('x', 63);
    }

    /**
     * FILTER_VALIDATE_EMAILで無効となる文字数の境界値のメールアドレス
     */
    public static function tooLongFormatValue(): string
    {
        return str_repeat('x', 64).'@'.
               str_repeat('x', 63).'.'.
               str_repeat('x', 63).'.'.
               str_repeat('x', 62);
    }

    /**
     * FILTER_VALIDATE_EMAILで無効となるローカルパートの文字数の境界値のメールアドレス
     */
    public static function tooLongLocalPartValue(): string
    {
        return str_repeat('x', 65).'@example.com';
    }

    /**
     * FILTER_VALIDATE_EMAILで無効となるドメインラベルの文字数の境界値のメールアドレス
     */
    public static function tooLongDomainLabelValue(): string
    {
        return 'test@'.str_repeat('x', 64).'.x';
    }

    /**
     * FILTER_VALIDATE_EMAILで無効となるトップレベルドメインの文字数の境界値のメールアドレス
     */
    public static function tooLongTopLevelDomainValue(): string
    {
        return 'test@x.'.str_repeat('x', 64);
    }

    // --- 例外メッセージ ---

    /** メールアドレスの最大文字数 */
    private const MAX_LENGTH = 255;

    /** メールアドレスが空の場合の例外メッセージ */
    public const EMPTY_MESSAGE = 'Email address is empty.';

    /** メールアドレスが無効なフォーマットの場合の例外メッセージ */
    public const INVALID_FORMAT_MESSAGE = "/Email address '.*' has an invalid format\./";

    /**
     * メールアドレスが最大文字数を超えている場合の例外メッセージ
     *
     * @param string $value テスト対象のメールアドレス
     */
    public static function tooLongMessage(string $value): string
    {
        return sprintf(
            "/Email address '.*' exceeds the maximum length of %d characters \(actual: %d\)\./",
            self::MAX_LENGTH,
            mb_strlen($value),
        );
    }
}
