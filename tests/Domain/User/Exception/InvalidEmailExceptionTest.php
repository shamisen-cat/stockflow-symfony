<?php

declare(strict_types=1);

namespace App\Tests\Domain\User\Exception;

use App\Domain\User\Exception\InvalidEmailException;
use App\Domain\User\ValueObject\Email\Email;
use App\Domain\User\ValueObject\Email\EmailValidationResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InvalidEmailExceptionTest extends TestCase
{
    #[Test]
    public function fromValidationResultThrowsWhenResultIsValid(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cannot create InvalidEmailException from VALID result.');

        InvalidEmailException::fromValidationResult(
            email: 'test@example.com',
            result: EmailValidationResult::VALID,
        );
    }

    #[Test]
    #[DataProvider('provideInvalidResultCases')]
    public function fromValidationResultReturnsExpectedPropertiesForEachInvalidResult(
        string $email,
        EmailValidationResult $result,
        string $message,
    ): void {
        $exception = InvalidEmailException::fromValidationResult($email, $result);

        self::assertSame($message, $exception->getMessage());
        self::assertSame($email, $exception->email);
        self::assertSame($result, $exception->result);
    }

    /**
     * @return iterable<string, array{string, EmailValidationResult, string}>
     */
    public static function provideInvalidResultCases(): iterable
    {
        yield 'empty' => [
            '',
            EmailValidationResult::EMPTY,
            'Email must not be empty.',
        ];

        yield 'too_long' => [
            str_repeat('a', 256),
            EmailValidationResult::TOO_LONG,
            sprintf('Email must not exceed %d characters.', Email::MAX_LENGTH),
        ];

        yield 'invalid_format' => [
            'invalid-format',
            EmailValidationResult::INVALID_FORMAT,
            'Email format is invalid.',
        ];
    }
}
