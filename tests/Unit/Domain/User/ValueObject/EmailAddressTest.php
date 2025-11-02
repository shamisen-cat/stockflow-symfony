<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\User\ValueObject;

use App\Domain\Shared\ValueObject\AbstractValueObject;
use App\Domain\User\Exception\EmailAddressException;
use App\Domain\User\ValueObject\EmailAddress;
use App\Tests\DataProvider\EmailAddressProvider;
use App\Tests\Helper\EmailAddressTestHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * メールアドレスの値オブジェクトのユニットテスト
 *
 * 正常系:
 * - 特殊文字を含むRFC準拠のメールアドレスで値オブジェクトが正常に生成されること
 *
 * 異常系:
 * - 空文字のメールアドレスで例外がスローされること
 * - 最大文字数を超えているメールアドレスで例外がスローされること
 * - フォーマットが無効なメールアドレスで例外がスローされること
 *
 * 等価性:
 * - 同じ値の値オブジェクトが等価であること
 * - 異なる値の値オブジェクトが等価でないこと
 * - 異なるクラスの値オブジェクトが等価でないこと
 */
final class EmailAddressTest extends TestCase
{
    // --- 正常系 ---

    /**
     * 特殊文字を含むRFC準拠のメールアドレスで値オブジェクトが正常に生成されることを検証する。
     *
     * @param string $value 有効なメールアドレス
     */
    #[DataProvider('validEmailValuesProvider')]
    public function testOfCreatesValidEmailAddress(string $value): void
    {
        // Act
        $email = EmailAddress::of($value);

        // Assert
        $this->assertInstanceOf(EmailAddress::class, $email);
        $this->assertSame($value, $email->value());
        $this->assertSame($value, $email->toString());
        $this->assertSame($value, $email->__toString());
    }

    // --- 異常系 ---

    /**
     * 空文字のメールアドレスで例外がスローされることを検証する。
     */
    public function testOfThrowsExceptionWhenEmailValueIsEmpty(): void
    {
        // Assert
        $this->expectException(EmailAddressException::class);
        $this->expectExceptionMessage(EmailAddressTestHelper::EMPTY_MESSAGE);

        // Act
        EmailAddress::of('');
    }

    /**
     * 最大文字数を超えているメールアドレスで例外がスローされることを検証する。
     */
    public function testOfThrowsExceptionWhenEmailValueExceedsMaxLength(): void
    {
        // Arrange
        $value = EmailAddressTestHelper::tooLongValue();

        // Assert
        $this->expectException(EmailAddressException::class);
        $this->expectExceptionMessageMatches(EmailAddressTestHelper::tooLongMessage($value));

        // Act
        EmailAddress::of($value);
    }

    /**
     * フォーマットが無効なメールアドレスで例外がスローされることを検証する。
     *
     * @param string $value フォーマットが無効なメールアドレス
     */
    #[DataProvider('invalidEmailFormatValuesProvider')]
    public function testOfThrowsExceptionForInvalidEmailFormat(string $value): void
    {
        // Assert
        $this->expectException(EmailAddressException::class);
        $this->expectExceptionMessageMatches(EmailAddressTestHelper::INVALID_FORMAT_MESSAGE);

        // Act
        EmailAddress::of($value);
    }

    // --- 等価性 ---

    /**
     * 同じ値の値オブジェクトが等価であることを検証する。
     */
    public function testEqualsReturnsTrueForSameValue(): void
    {
        // Arrange
        $value = EmailAddressTestHelper::TEST_VALUE;

        // Act
        $email = EmailAddress::of($value);
        $other = EmailAddress::of($value);

        // Assert
        $this->assertTrue($email->equals($other));
    }

    /**
     * 異なる値の値オブジェクトが等価でないことを検証する。
     */
    public function testEqualsReturnsFalseForDifferentValues(): void
    {
        // Act
        $email = EmailAddress::of(EmailAddressTestHelper::TEST_VALUE);
        $other = EmailAddress::of(EmailAddressTestHelper::DIFFERENT_VALUE);

        // Assert
        $this->assertFalse($email->equals($other));
    }

    /**
     * 異なるクラスの値オブジェクトが等価でないことを検証する。
     */
    public function testEqualsReturnsFalseForDifferentClass(): void
    {
        // Arrange
        $value = EmailAddressTestHelper::TEST_VALUE;

        // Act
        $email = EmailAddress::of($value);
        $other = new class($value) extends AbstractValueObject {
            private readonly string $value;

            public function __construct(string $value)
            {
                $this->value = $value;
            }

            public function value(): mixed
            {
                return $this->value;
            }
        };

        // Assert
        $this->assertFalse($email->equals($other));
    }

    // --- データプロバイダー ---

    /**
     * 有効なメールアドレスのデータプロバイダー
     *
     * @return array<string, array{string}>
     */
    public static function validEmailValuesProvider(): array
    {
        return EmailAddressProvider::validValues();
    }

    /**
     * フォーマットが無効なメールアドレスのデータプロバイダー
     *
     * @return array<string, array{string}>
     */
    public static function invalidEmailFormatValuesProvider(): array
    {
        return EmailAddressProvider::invalidFormatValues();
    }
}
