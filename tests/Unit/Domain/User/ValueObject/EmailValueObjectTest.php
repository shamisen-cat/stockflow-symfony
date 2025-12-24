<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\ValueObject;

use App\Domain\User\Exception\EmailException;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\UnverifiedEmail;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * メールの値オブジェクトのユニットテスト
 *
 * 正常系：
 * - 有効な値でインスタンスが正常に生成されること
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
 * - 同じ値の他のメールの値オブジェクトが等価であること
 * - 異なる値の他のメールの値オブジェクトが等価でないこと
 */
class EmailValueObjectTest extends TestCase
{
    private const string TEST_VALUE  = 'test-123@example.com';
    private const string OTHER_VALUE = 'test_123@example.com';

    // --- 正常系 ---

    /**
     * 有効な値でインスタンスが正常に生成されること
     *
     * @param class-string<Email|UnverifiedEmail> $class 値オブジェクトの完全修飾クラス名
     * @param string                              $value 有効な値
     */
    #[Test]
    #[DataProvider('provideValidValues')]
    public function testOfCreatesInstanceWithValidValue(string $class, string $value): void
    {
        // Act
        $email = $class::of($value);

        // Assert
        $this->assertInstanceOf($class, $email);
        $this->assertSame($value, $email->value());
        $this->assertSame($value, $email->__toString());
    }

    // --- 異常系 ---

    /**
     * 空文字で例外がスローされること
     *
     * @param class-string<Email|UnverifiedEmail> $class 値オブジェクトの完全修飾クラス名
     */
    #[Test]
    #[DataProvider('provideClasses')]
    public function testOfThrowsExceptionWithEmptyValue(string $class): void
    {
        // Arrange
        $value = '';

        // Assert
        $this->expectException(EmailException::class);
        $this->expectExceptionMessage('Email is empty.');

        // Act
        $class::of($value);
    }

    /**
     * 最大文字数を超えている値で例外がスローされること
     *
     * @param class-string<Email|UnverifiedEmail> $class 値オブジェクトの完全修飾クラス名
     */
    #[Test]
    #[DataProvider('provideClasses')]
    public function testOfThrowsExceptionWithExceedingMaximumLengthValue(string $class): void
    {
        // Arrange
        $value = str_repeat('a', 64).'@'.
                 str_repeat('b', 63).'.'.
                 str_repeat('c', 63).'.'.
                 str_repeat('d', 63);

        // Assert
        $this->expectException(EmailException::class);
        $this->expectExceptionMessage('exceeds maximum length');

        // Act
        $class::of($value);
    }

    /**
     * 無効な形式の値で例外がスローされること
     *
     * @param class-string<Email|UnverifiedEmail> $class 値オブジェクトの完全修飾クラス名
     * @param string                              $value 無効な形式の値
     */
    #[Test]
    #[DataProvider('provideInvalidFormatValues')]
    public function testOfThrowsExceptionWithInvalidFormatValue(string $class, string $value): void
    {
        // Assert
        $this->expectException(EmailException::class);
        $this->expectExceptionMessage('invalid format');

        // Act
        $class::of($value);
    }

    // --- 等価性 ---

    /**
     * 同じ値の値オブジェクトが等価であること
     *
     * @param class-string<Email|UnverifiedEmail> $class 値オブジェクトの完全修飾クラス名
     */
    #[Test]
    #[DataProvider('provideClasses')]
    public function testEqualsReturnsTrueWithSameValue(string $class): void
    {
        // Arrange
        $value = self::TEST_VALUE;

        // Act
        $email = $class::of($value);
        $other = $class::of($value);

        // Assert
        $this->assertTrue($email->equals($other));
    }

    /**
     * 異なる値の値オブジェクトが等価でないこと
     */
    #[Test]
    #[DataProvider('provideClasses')]
    public function testEqualsReturnsFalseWithDifferentValues(string $class): void
    {
        // Arrange
        $value      = self::TEST_VALUE;
        $otherValue = self::OTHER_VALUE;

        // Act
        $email = $class::of($value);
        $other = $class::of($otherValue);

        // Assert
        $this->assertFalse($email->equals($other));
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
        $email = Email::of($value);
        $other = UnverifiedEmail::of($value);

        // Assert
        /* @phpstan-ignore-next-line */
        $this->assertFalse($email->equals($other));
    }

    /**
     * 同じ値の他のメールの値オブジェクトが等価であること
     */
    #[Test]
    public function testIsSameValueReturnsTrueWithSameValue(): void
    {
        // Arrange
        $value = self::TEST_VALUE;

        // Act
        $email = Email::of($value);
        $other = UnverifiedEmail::of($value);

        // Assert
        $this->assertTrue($email->isSameValue($other));
    }

    /**
     * 異なる値の他のメールの値オブジェクトが等価でないこと
     */
    #[Test]
    public function testIsSameValueReturnsFalseWithDifferentValues(): void
    {
        // Arrange
        $value      = self::TEST_VALUE;
        $otherValue = self::OTHER_VALUE;

        // Act
        $email = Email::of($value);
        $other = UnverifiedEmail::of($otherValue);

        // Assert
        $this->assertFalse($email->isSameValue($other));
    }

    // --- データプロバイダー ---

    /**
     * 完全修飾クラス名のデータプロバイダー
     *
     * @return array<string, array{0: class-string}> 完全修飾クラス名の配列
     */
    public static function provideClasses(): array
    {
        return [
            'Email'           => [Email::class],
            'UnverifiedEmail' => [UnverifiedEmail::class],
        ];
    }

    /**
     * 有効な値のデータプロバイダー
     *
     * @return array<string, array{0: class-string, 1: string}> 有効な値の配列
     */
    public static function provideValidValues(): array
    {
        return self::generatePatterns([
            // 基本形式
            'standard'  => 'test@example.com',
            'subdomain' => 'test@sub.example.com',

            // ローカル
            'local_numeric'                  => '123@example.com',
            'local_dot'                      => 'test.dot@example.com',
            'local_double_hyphen'            => 'test--hyphen@example.com',
            'local_starting_with_plus'       => '+test@example.com',
            'local_starting_with_hyphen'     => '-test@example.com',
            'local_starting_with_underscore' => '_test@example.com',
            'local_ending_with_plus'         => 'test+@example.com',
            'local_ending_with_hyphen'       => 'test-@example.com',
            'local_ending_with_underscore'   => 'test_@example.com',

            // ドメイン・TLD
            'domain_numeric'     => 'test@123.com',
            'domain_hyphen'      => 'test@hyphen-example.com',
            'tld_ending_numeric' => 'test@example.com123',

            // TODO: バリデーション候補
            'local_quote'        => "test'quote@example.com",
            'local_double_quote' => '"test"@example.com',
            'local_backtick'     => 'test`backtick@example.com',
            'local_slash'        => 'test/slash@example.com',
            'local_injection'    => 'test%0A@example.com',

            'domain_double_hyphen' => 'test@example--hyphen.com',
            'domain_ip_address'    => 'test@[127.0.0.1]',
            'domain_ipv6'          => 'test@[IPv6:2001:db8::1]',

            // 文字数の境界値
            'minimum_length' => 'a@b.c',
            'maximum_length' => str_repeat('a', 64).'@'.
                                str_repeat('b', 63).'.'.
                                str_repeat('c', 61).'.'.
                                str_repeat('d', 63),
        ]);
    }

    /**
     * 無効な形式の値のデータプロバイダー
     *
     * @return array<string, array{0: class-string, 1: string}> 無効な形式の値の配列
     */
    public static function provideInvalidFormatValues(): array
    {
        return self::generatePatterns([
            // ローカル：形式不備
            'local_missing'      => '@example.com',
            'local_double_dot'   => 'test..dot@example.com',
            'local_starting_dot' => '.test@example.com',
            'local_ending_dot'   => 'test.@example.com',

            // ローカル：禁止記号・セキュリティー
            'local_space'             => 'test space@example.com',
            'local_control_char'      => "test\nline@example.com",
            'local_semicolon'         => 'test;semicolon@example.com',
            'local_bracket'           => 'test[bracket]@example.com',
            'local_backslash'         => 'test\\backslash@example.com',
            'local_quoted_pair_space' => 'test\\ @example.com',

            // アットマーク
            'at_missing'    => 'test',
            'at_double'     => 'test@@example.com',
            'at_full_width' => 'test＠example.com',

            // ドメイン
            'domain_missing'          => 'test@',
            'domain_double_dot'       => 'test@example..dot.com',
            'domain_hyphen_after_dot' => 'test@example.-hyphen.com',
            'domain_starting_dot'     => 'test@.example.com',
            'domain_starting_hyphen'  => 'test@-example.com',
            'domain_ending_hyphen'    => 'test@example-.com',

            // TLD
            'tld_missing'          => 'test@example',
            'tld_numeric'          => 'test@example.123',
            'tld_starting_numeric' => 'test@example.123com',
            'tld_starting_hyphen'  => 'test@example.-com',
            'tld_ending_dot'       => 'test@example.com.',

            // 文字数の境界値
            'maximum_length_exceeding_local'  => str_repeat('a', 65).'@example.com',
            'maximum_length_exceeding_domain' => 'test@'.str_repeat('b', 64).'.com',
            'maximum_length_exceeding_tld'    => 'test@example.'.str_repeat('c', 64),
            'maximum_length_exceeding'        => str_repeat('a', 64).'@'.
                                                 str_repeat('b', 63).'.'.
                                                 str_repeat('c', 62).'.'.
                                                 str_repeat('d', 63),
        ]);
    }

    /**
     * 完全修飾クラス名と値の配列を生成する。
     *
     * @param array<string, string> $values 値の配列
     *
     * @return array<string, array{0: class-string<Email|UnverifiedEmail>, 1: string}> 完全修飾クラス名と値の配列
     */
    private static function generatePatterns(array $values): array
    {
        $patterns = [];
        foreach (self::provideClasses() as $classWrapper) {
            /** @var class-string<Email|UnverifiedEmail> $class */
            $class     = $classWrapper[0];
            $className = (new \ReflectionClass($class))->getShortName();

            foreach ($values as $key => $value) {
                $patterns["{$className}: '$key'"] = [$class, $value];
            }
        }

        return $patterns;
    }
}
