<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\ValueObject;

use App\Domain\Shared\ValueObject\AbstractValueObject;
use App\Domain\User\Exception\VerificationTokenException;
use App\Domain\User\ValueObject\EmailVerificationToken;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * メール認証トークンの値オブジェクトのユニットテスト
 *
 * 正常系：
 * - 有効な値でインスタンスが正常に生成されること
 *
 * 異常系：
 * - 空文字で例外がスローされること
 * - 最小文字数を満たしていない値で例外がスローされること
 * - 最大文字数を超えている値で例外がスローされること
 * - 無効な形式の値で例外がスローされること
 *
 * 等価性：
 * - 同じ値の値オブジェクトが等価であること
 * - 異なる値の値オブジェクトが等価でないこと
 * - 異なる値オブジェクトが等価でないこと
 */
class EmailVerificationTokenTest extends TestCase
{
    private const string TEST_VALUE  = '0123456789abcdef0123456789abcdef';
    private const string OTHER_VALUE = 'a123456789abcdef0123456789abcdef';

    // --- 正常系 ---

    /**
     * 有効な値でインスタンスが正常に生成されること
     *
     * @param string $value 有効な値
     */
    #[Test]
    #[DataProvider('provideValidValues')]
    public function testOfCreatesInstanceWithValidValue(string $value): void
    {
        // Act
        $token = EmailVerificationToken::of($value);

        // Assert
        $this->assertInstanceOf(EmailVerificationToken::class, $token);
        $this->assertSame($value, $token->value());
        $this->assertSame($value, $token->__toString());
    }

    // --- 異常系 ---

    /**
     * 空文字で例外がスローされること
     */
    #[Test]
    public function testOfThrowsExceptionWithEmptyValue(): void
    {
        // Arrange
        $value = '';

        // Assert
        $this->expectException(VerificationTokenException::class);
        $this->expectExceptionMessage('Verification token is empty.');

        // Act
        EmailVerificationToken::of($value);
    }

    /**
     * 最小文字数を満たしていない値で例外がスローされること
     */
    #[Test]
    public function testOfThrowsExceptionWithBelowMinimumLengthValue(): void
    {
        // Arrange
        $value = str_repeat('a', 31);

        // Assert
        $this->expectException(VerificationTokenException::class);
        $this->expectExceptionMessage('below minimum length');

        // Act
        EmailVerificationToken::of($value);
    }

    /**
     * 最大文字数を超えている値で例外がスローされること
     */
    #[Test]
    public function testOfThrowsExceptionWithExceedingMaximumLengthValue(): void
    {
        // Arrange
        $value = str_repeat('a', 256);

        // Assert
        $this->expectException(VerificationTokenException::class);
        $this->expectExceptionMessage('exceeds maximum length');

        // Act
        EmailVerificationToken::of($value);
    }

    /**
     * 無効な形式の値で例外がスローされること
     *
     * @param string $value 無効な形式の値
     */
    #[Test]
    #[DataProvider('provideInvalidFormatValues')]
    public function testOfThrowsExceptionWithInvalidFormatValue(string $value): void
    {
        // Arrange
        $token = $value;

        // Assert
        $this->expectException(VerificationTokenException::class);
        $this->expectExceptionMessage('invalid format');

        // Act
        EmailVerificationToken::of($token);
    }

    // --- 等価性 ---

    /**
     * 同じ値の値オブジェクトが等価であること
     */
    #[Test]
    public function testEqualsReturnsTrueWithSameValue(): void
    {
        // Arrange
        $value = self::TEST_VALUE;

        // Act
        $token = EmailVerificationToken::of($value);
        $other = EmailVerificationToken::of($value);

        // Assert
        $this->assertTrue($token->equals($other));
    }

    /**
     * 異なる値の値オブジェクトが等価でないこと
     */
    #[Test]
    public function testEqualsReturnsFalseWithDifferentValues(): void
    {
        // Arrange
        $value      = self::TEST_VALUE;
        $otherValue = self::OTHER_VALUE;

        // Act
        $token = EmailVerificationToken::of($value);
        $other = EmailVerificationToken::of($otherValue);

        // Assert
        $this->assertFalse($token->equals($other));
    }

    /**
     * 異なる値オブジェクトが等価でないこと
     */
    #[Test]
    public function testEqualsReturnsFalseWithDifferentClass(): void
    {
        // Arrange
        $value = self::TEST_VALUE;

        // Act
        $token = EmailVerificationToken::of($value);

        $other = new readonly class($value) extends AbstractValueObject {
            public function __construct(private string $value)
            {
            }

            public function value(): string
            {
                return $this->value;
            }
        };

        // Assert
        /* @phpstan-ignore-next-line */
        $this->assertFalse($token->equals($other));
    }

    // --- データプロバイダー ---

    /**
     * 有効な値のデータプロバイダー
     *
     * @return array<string, array{0: string}> 有効な値の配列
     */
    public static function provideValidValues(): array
    {
        return [
            'typical'      => [self::TEST_VALUE],
            'numeric_only' => [str_repeat('0123456789', 4)],
            'hex_only'     => [str_repeat('abcdef', 6)],

            // 文字数の境界値
            'minimum_length' => [str_repeat('a', 32)],
            'maximum_length' => [str_repeat('a', 255)],
        ];
    }

    /**
     * 無効な形式の値のデータプロバイダー
     *
     * @return array<string, array{0: string}> 無効な形式の値の配列
     */
    public static function provideInvalidFormatValues(): array
    {
        $base = self::TEST_VALUE;

        return [
            'invalid_hex'       => [substr_replace($base, 'g', 0, 1)],
            'all_uppercase'     => [strtoupper($base)],
            'partial_uppercase' => [substr_replace($base, 'A', 0, 1)],
            'multi_byte'        => [substr_replace($base, 'あ', 0, 1)],

            // 記号・空白文字
            'with_hyphen'  => [substr_replace($base, '-', 16, 1)],
            'with_space'   => [substr_replace($base, ' ', 16, 1)],
            'with_newline' => [substr_replace($base, "\n", 16, 1)],
        ];
    }
}
