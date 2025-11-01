<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\User\ValueObject;

use App\Domain\User\Exception\EmailAddressException;
use App\Domain\User\ValueObject\EmailAddress;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * メールアドレスの値オブジェクトのユニットテスト
 *
 * 正常系:
 * - 特殊文字を含むRFC準拠のメールアドレスでEmailAddressが正常に生成されること
 *
 * 異常系:
 * - 空文字のメールアドレスで例外がスローされること
 * - 最大文字数を超えたメールアドレスで例外がスローされること
 * - 無効な形式のメールアドレスで例外がスローされること
 *
 * 等価性:
 * - 同じ値のEmailAddressが等価であること
 */
final class EmailAddressTest extends TestCase
{
    // --- 正常系 ---

    /**
     * 特殊文字を含むRFC準拠のメールアドレスでEmailAddressが正常に生成されることを検証する。
     *
     * @param string $value テスト対象の有効なメールアドレス
     */
    #[DataProvider('validEmailAddressesProvider')]
    public function testOfCreatesValidAddress(string $value): void
    {
        // Act
        $email = EmailAddress::of($value);

        // Assert
        $this->assertInstanceOf(EmailAddress::class, $email);
        $this->assertSame($value, $email->value());
        $this->assertSame($value, $email->toString());
    }

    // --- 異常系 ---

    /**
     * 空文字のメールアドレスで例外がスローされることを検証する。
     */
    public function testOfThrowsExceptionWhenEmailAddressIsEmpty(): void
    {
        // Arrange
        $value = '';

        // Assert
        $this->expectException(EmailAddressException::class);
        $this->expectExceptionMessage('Email address is empty.');

        // Act
        EmailAddress::of($value);
    }

    /**
     * 最大文字数を超えたメールアドレスで例外がスローされることを検証する。
     */
    public function testOfThrowsExceptionWhenEmailAddressExceedsMaxLength(): void
    {
        // Arrange
        $value = str_repeat('x', 64).'@'.
                 str_repeat('x', 63).'.'.
                 str_repeat('x', 63).'.'.
                 str_repeat('x', 63);
        $maxLength = 255;

        // Assert
        $this->expectException(EmailAddressException::class);
        $this->expectExceptionMessageMatches(sprintf(
            "/Email address '.*' exceeds the maximum length of %d characters \(actual: %d\)\./",
            $maxLength,
            mb_strlen($value),
        ));

        // Act
        EmailAddress::of($value);
    }

    /**
     * 無効な形式のメールアドレスで例外がスローされることを検証する。
     *
     * @param string $value テスト対象の無効な形式のメールアドレス
     */
    #[DataProvider('invalidEmailAddressFormatsProvider')]
    public function testOfThrowsExceptionForInvalidEmailAddressFormat(string $value): void
    {
        // Assert
        $this->expectException(EmailAddressException::class);
        $this->expectExceptionMessageMatches("/Email address '.*' has an invalid format\./");

        // Act
        EmailAddress::of($value);
    }

    // --- 等価性 ---

    /**
     * 同じ値のEmailAddressが等価であることを検証する。
     */
    public function testOfCreatesEqualObjectsForSameValue(): void
    {
        // Arrange
        $value = 'test@example.com';

        // Act
        $email1 = EmailAddress::of($value);
        $email2 = EmailAddress::of($value);

        // Assert
        $this->assertEquals($email1, $email2);
    }

    // --- データプロバイダー ---

    /**
     * 有効なメールアドレスのデータプロバイダー
     *
     * @return array <string, array{string}>
     */
    public static function validEmailAddressesProvider(): array
    {
        // Arrange
        return [
            'standard_format'          => ['test@example.com'],
            'numeric_local_part'       => ['123@example.com'],
            'numeric_domain'           => ['test@123.com'],
            'dot_in_local_part'        => ['test.dot@example.com'],
            'hyphen_in_local_part'     => ['test-hyphen@example.com'],
            'underscore_in_local_part' => ['test_underscore@example.com'],
            'plus_in_local_part'       => ['test+plus@example.com'],
            'hyphen_in_domain'         => ['test@example-hyphen.com'],
            'shortest_valid_form'      => ['x@x.x'],
            'max_length_edge_case'     => [
                str_repeat('x', 64).'@'.
                str_repeat('x', 63).'.'.
                str_repeat('x', 63).'.'.
                str_repeat('x', 61),
            ],

            // RFC-compliant addresses that pass the current validation.
            'percent_in_local_part'           => ['test%percent@example.com'],
            'double_dot_in_quoted_local_part' => ['"test..dot"@example.com'],
        ];
    }

    /**
     * 無効な形式のメールアドレスのデータプロバイダー
     *
     * @return array <string, array{string}>
     */
    public static function invalidEmailAddressFormatsProvider(): array
    {
        // Arrange
        return [
            'missing_local_part'         => ['@example.com'],
            'missing_domain'             => ['test'],
            'missing_domain_part'        => ['test@'],
            'missing_top_level_domain'   => ['test@example'],
            'leading_dot_in_local_part'  => ['.test@example.com'],
            'trailing_dot_in_local_part' => ['test.@example.com'],
            'double_dot_in_local_part'   => ['test..dot@example.com'],
            'double_at'                  => ['test@@example.com'],
            'leading_dot_in_domain'      => ['test@.example.com'],
            'double_dot_in_domain'       => ['test@example..com'],
            'leading_hyphen_in_domain'   => ['test@-example.com'],
            'trailing_hyphen_in_domain'  => ['test@example-.com'],
            'local_part_too_long'        => [str_repeat('x', 65).'@x.x'],
            'top_level_domain_too_long'  => ['x@x'.str_repeat('x', 64)],
            'max_length_invalid'         => [
                str_repeat('x', 64).'@'.
                str_repeat('x', 63).'.'.
                str_repeat('x', 63).'.'.
                str_repeat('x', 62),
            ],

            // RFC-compliant addresses that are rejected by the current validation.
            'numeric_top_level_domain'   => ['test@example.123'],
            'space_in_quoted_local_part' => ['"test space"@example.com'],
        ];
    }
}
