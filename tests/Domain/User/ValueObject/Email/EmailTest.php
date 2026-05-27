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
    #[Test]
    public function ofReturnsExpectedValueForValidEmail(): void
    {
        $email   = 'test@example.com';
        $emailVo = Email::of($email);

        self::assertSame($email, $emailVo->value());
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
        $email = 'test@example.com';
        $left  = Email::of($email);
        $right = Email::of($email);

        self::assertTrue($left->equals($right));
    }

    #[Test]
    public function equalsReturnsFalseWhenValuesAreNotEqual(): void
    {
        $left  = Email::of('test@example.com');
        $right = Email::of('uest@example.com');

        self::assertFalse($left->equals($right));
    }

    #[Test]
    public function toStringReturnsExpectedValue(): void
    {
        $email   = 'test@example.com';
        $emailVo = Email::of($email);

        self::assertSame($email, (string) $emailVo);
    }

    /**
     * @return iterable<string, array{string, EmailValidationResult}>
     */
    public static function provideEmailAndExpectedResult(): iterable
    {
        yield 'valid' => [
            'test@example.com',
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
