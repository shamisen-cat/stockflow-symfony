<?php

declare(strict_types=1);

namespace App\Tests\Domain\User\Exception;

use App\Domain\User\Exception\InvalidPlainPasswordException;
use App\Domain\User\ValueObject\Password\PlainPassword;
use App\Domain\User\ValueObject\Password\PlainPasswordValidationResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InvalidPlainPasswordExceptionTest extends TestCase
{
    #[Test]
    public function fromValidationResultThrowsWhenResultIsValid(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageIs('Cannot create InvalidPlainPasswordException from VALID result.');

        InvalidPlainPasswordException::fromValidationResult(PlainPasswordValidationResult::VALID);
    }

    #[Test]
    #[DataProvider('provideInvalidResultCases')]
    public function fromValidationResultReturnsExpectedPropertiesForEachInvalidResult(
        PlainPasswordValidationResult $result,
        string $message,
    ): void {
        $exception = InvalidPlainPasswordException::fromValidationResult($result);

        self::assertSame($message, $exception->getMessage());
        self::assertSame($result, $exception->result);
    }

    /**
     * @return iterable<string, array{PlainPasswordValidationResult, string}>
     */
    public static function provideInvalidResultCases(): iterable
    {
        yield 'empty' => [
            PlainPasswordValidationResult::EMPTY,
            'Plain password must not be empty.',
        ];

        yield 'too_short' => [
            PlainPasswordValidationResult::TOO_SHORT,
            sprintf('Plain password must be at least %d characters.', PlainPassword::MIN_LENGTH),
        ];

        yield 'too_long' => [
            PlainPasswordValidationResult::TOO_LONG,
            sprintf('Plain password must not exceed %d characters.', PlainPassword::MAX_LENGTH),
        ];
    }
}
