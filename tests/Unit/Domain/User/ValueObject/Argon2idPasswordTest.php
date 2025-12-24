<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\ValueObject;

use App\Domain\Shared\ValueObject\AbstractValueObject;
use App\Domain\User\Exception\PasswordException;
use App\Domain\User\ValueObject\Argon2idPassword;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Argon2idパスワードの値オブジェクトのユニットテスト
 *
 * 正常系：
 * - 有効な値でインスタンスが正常に生成されること
 *
 * 異常系：
 * - 空文字で例外がスローされること
 * - 最小文字数を満たしていない値で例外がスローされること
 * - 最大文字数を超えている値で例外がスローされること
 * - Argon2idが使用されていない値で例外がスローされること
 *
 * 等価性：
 * - 同じ値の値オブジェクトが等価であること
 * - 異なる値の値オブジェクトが等価でないこと
 * - 異なる値オブジェクトが等価でないこと
 */
class Argon2idPasswordTest extends TestCase
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
        $password = Argon2idPassword::of($value);

        // Assert
        $this->assertInstanceOf(Argon2idPassword::class, $password);
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
        Argon2idPassword::of($value);
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
        Argon2idPassword::of($value);
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
        Argon2idPassword::of($value);
    }

    /**
     * Argon2idが使用されていない値で例外がスローされること
     *
     * @param string $value Argon2idが使用されていない値
     */
    #[Test]
    #[DataProvider('provideNotArgon2idValues')]
    public function testOfThrowsExceptionWithNotArgon2idValue(string $value): void
    {
        // Assert
        $this->expectException(PasswordException::class);
        $this->expectExceptionMessage("Password algorithm is not 'Argon2id'");

        // Act
        Argon2idPassword::of($value);
    }

    // --- 等価性 ---

    /**
     * 同じ値の値オブジェクトが等価であること
     */
    #[Test]
    public function testEqualsReturnsTrueWithSameValue(): void
    {
        // Arrange
        $value = self::generateArgon2idHash(self::TEST_VALUE);

        // Act
        $password = Argon2idPassword::of($value);
        $other    = Argon2idPassword::of($value);

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
        $value      = self::generateArgon2idHash(self::TEST_VALUE);
        $otherValue = self::generateArgon2idHash(self::OTHER_VALUE);

        // Act
        $password = Argon2idPassword::of($value);
        $other    = Argon2idPassword::of($otherValue);

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
        $value = self::generateArgon2idHash(self::TEST_VALUE);

        // Act
        $password = Argon2idPassword::of($value);

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
            'standard' => [self::generateArgon2idHash(self::TEST_VALUE)],
        ];
    }

    /**
     * Argon2idが使用されていない値のデータプロバイダー
     *
     * @return array<string, array{0: string}> Argon2idが使用されていない値の配列
     */
    public static function provideNotArgon2idValues(): array
    {
        return [
            'plain'   => [self::TEST_VALUE],
            'bcrypt'  => [password_hash(self::TEST_VALUE, PASSWORD_BCRYPT)],
            'argon2i' => [password_hash(self::TEST_VALUE, PASSWORD_ARGON2I)],
        ];
    }

    /**
     * 平文パスワードからArgon2idハッシュ文字列を生成する。
     *
     * @param string $plainPassword 平文パスワード
     *
     * @return string Argon2idハッシュ文字列
     */
    private static function generateArgon2idHash(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_ARGON2ID);
    }
}
