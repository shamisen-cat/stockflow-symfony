<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\ValueObject;

use App\Domain\Shared\ValueObject\AbstractValueObject;
use App\Domain\User\Exception\PasswordException;
use App\Domain\User\ValueObject\PlainPassword;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * 平文パスワードの値オブジェクトのユニットテスト
 *
 * 正常系：
 * - 有効な値でインスタンスが正常に生成されること
 *
 * 異常系：
 * - 空文字で例外がスローされること
 * - 最小文字数を満たしていない値で例外がスローされること
 * - 最大文字数を超えている値で例外がスローされること
 *
 * 等価性：
 * - 同じ値の値オブジェクトが等価であること
 * - 異なる値の値オブジェクトが等価でないこと
 * - 異なる値オブジェクトが等価でないこと
 */
class PlainPasswordTest extends TestCase
{
    private const string TEST_VALUE  = 'Te$tP@ssw0rd';
    private const string OTHER_VALUE = 'te$tP@ssw0rd';

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
        $password = PlainPassword::of($value);

        // Assert
        $this->assertInstanceOf(PlainPassword::class, $password);
        $this->assertSame($value, $password->value());
        $this->assertSame($value, $password->__toString());
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
        $this->expectException(PasswordException::class);
        $this->expectExceptionMessage('Password is empty.');

        // Act
        PlainPassword::of($value);
    }

    /**
     * 最小文字数を満たしていない値で例外がスローされること
     */
    #[Test]
    public function testOfThrowsExceptionWithBelowMinimumLengthValue(): void
    {
        // Arrange
        $value = str_repeat('a', 11);

        // Assert
        $this->expectException(PasswordException::class);
        $this->expectExceptionMessage('below minimum length');

        // Act
        PlainPassword::of($value);
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
        $this->expectException(PasswordException::class);
        $this->expectExceptionMessage('exceeds maximum length');

        // Act
        PlainPassword::of($value);
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
        $password = PlainPassword::of($value);
        $other    = PlainPassword::of($value);

        // Assert
        $this->assertTrue($password->equals($other));
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
        $password = PlainPassword::of($value);
        $other    = PlainPassword::of($otherValue);

        // Assert
        $this->assertFalse($password->equals($other));
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
        $password = PlainPassword::of($value);

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
        $this->assertFalse($password->equals($other));
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
            // 基本形式
            'standard' => ['Te$t-P@ssw0rd_123!'],

            // 文字数の境界値
            'minimum_length' => [str_repeat('a', 12)],
            'maximum_length' => [str_repeat('a', 255)],
        ];
    }
}
