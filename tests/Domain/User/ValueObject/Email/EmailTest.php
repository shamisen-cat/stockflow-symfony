<?php

declare(strict_types=1);

namespace App\Tests\Domain\User\ValueObject\Email;

use App\Domain\User\Exception\InvalidEmailException;
use App\Domain\User\ValueObject\Email\Email;
use App\Domain\User\ValueObject\Email\EmailValidationResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    private const string VALID_EMAIL = 'test@example.com';

    #[Test]
    public function ofReturnsExpectedValueForValidEmail(): void
    {
        $email = self::VALID_EMAIL;

        $emailVo = Email::of($email);
        $value   = $emailVo->value();

        self::assertSame($email, $value);
    }

    #[Test]
    #[DataProvider('provideInvalidEmail')]
    public function ofThrowsWhenEmailIsInvalid(string $email): void
    {
        $this->expectException(InvalidEmailException::class);

        Email::of($email);
    }

    #[Test]
    #[DataProvider('provideEmailAndExpectedResult')]
    public function validateReturnsExpectedResultForEachEmail(
        string $email,
        EmailValidationResult $expected,
    ): void {
        $result = Email::validate($email);

        self::assertSame($expected, $result);
    }

    #[Test]
    public function equalsReturnsTrueWhenValuesAreEqual(): void
    {
        $email = self::VALID_EMAIL;

        $left  = Email::of($email);
        $right = Email::of($email);

        self::assertTrue($left->equals($right));
    }

    #[Test]
    public function equalsReturnsFalseWhenValuesAreNotEqual(): void
    {
        $email = self::VALID_EMAIL;
        $other = 'u'.substr($email, 1);

        $left  = Email::of($email);
        $right = Email::of($other);

        self::assertFalse($left->equals($right));
    }

    #[Test]
    public function toStringReturnsExpectedValue(): void
    {
        $email = self::VALID_EMAIL;

        $emailVo = Email::of($email);
        $string  = (string) $emailVo;

        self::assertSame($email, $string);
    }

    /**
     * @return iterable<string, array{string, EmailValidationResult}>
     */
    public static function provideEmailAndExpectedResult(): iterable
    {
        yield 'valid' => [
            self::VALID_EMAIL,
            EmailValidationResult::VALID,
        ];

        yield 'empty' => [
            '',
            EmailValidationResult::EMPTY,
        ];

        yield 'too_long' => [
            str_repeat('a', 256),
            EmailValidationResult::TOO_LONG,
        ];

        // TODO: provideEmailAndExpectedResult に INVALID_FORMAT の yield を追加
        yield 'invalid_format' => [
            'invalid-format',
            EmailValidationResult::INVALID_FORMAT,
        ];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideInvalidEmail(): iterable
    {
        foreach (self::provideEmailAndExpectedResult() as $name => [$email, $expected]) {
            if ($expected === EmailValidationResult::VALID) {
                continue;
            }

            yield $name => [$email];
        }
    }
}
