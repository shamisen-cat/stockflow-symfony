<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\ValueObject;

use App\Domain\Shared\ValueObject\AbstractValueObject;
use App\Domain\User\Exception\UserNameException;
use App\Domain\User\ValueObject\UserName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * ユーザー名の値オブジェクトのユニットテスト
 *
 * 正常系：
 * - 有効な値でインスタンスが正常に生成されること
 * - 値の設定がないインスタンスが正常に生成されること
 *
 * 異常系：
 * - 空文字で例外がスローされること
 * - 最大文字数を超えている値で例外がスローされること
 * - 無効な形式の値で例外がスローされること
 *
 * 等価性：
 * - 同じ値の値オブジェクトが等価であること
 * - 異なる値の値オブジェクトが等価でないこと
 * - 異なる値オブジェクトが等価でないこと
 */
class UserNameTest extends TestCase
{
    private const string TEST_VALUE  = 'TestUser';
    private const string OTHER_VALUE = 'testUser';

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
        $name = UserName::of($value);

        // Assert
        $this->assertInstanceOf(UserName::class, $name);
        $this->assertSame($value, $name->value());
        $this->assertSame($value, $name->__toString());
        $this->assertFalse($name->isNone());
    }

    /**
     * 値の設定がないインスタンスが正常に生成されること
     */
    #[Test]
    public function testNoneCreatesInstance(): void
    {
        // Act
        $name = UserName::none();

        // Assert
        $this->assertInstanceOf(UserName::class, $name);
        $this->assertNull($name->value());
        $this->assertSame('', $name->__toString());
        $this->assertTrue($name->isNone());
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
        $this->expectException(UserNameException::class);
        $this->expectExceptionMessage('User name is empty.');

        // Act
        UserName::of($value);
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
        $this->expectException(UserNameException::class);
        $this->expectExceptionMessage('exceeds maximum length');

        // Act
        UserName::of($value);
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
        $name = $value;

        // Assert
        $this->expectException(UserNameException::class);
        $this->expectExceptionMessage('invalid format');

        // Act
        UserName::of($name);
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
        $name  = UserName::of($value);
        $other = UserName::of($value);

        // Assert
        $this->assertTrue($name->equals($other));
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
        $name  = UserName::of($value);
        $other = UserName::of($otherValue);

        // Assert
        $this->assertFalse($name->equals($other));
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
        $name = UserName::of($value);

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
        $this->assertFalse($name->equals($other));
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
            'typical'          => ['Te$t_Us3r-123'],
            'half_width_space' => ['Test User'],
            'full_width_space' => ['テスト　ユーザー'],

            // 文字数の境界値
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
        return [
            // 前後の空白文字
            'leading_space'         => [' LeadingSpace'],
            'trailing_space'        => ['TrailingSpace '],
            'edge_full_width_space' => ['　FullWidthSpace　'],

            // 無効な制御文字
            'with_tab'             => ["Test\tTab"],
            'with_newline'         => ["Test\nNewline"],
            'with_carriage_return' => ["Test\rCarriageReturn"],
        ];
    }
}
