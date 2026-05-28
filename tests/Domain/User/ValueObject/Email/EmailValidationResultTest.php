<?php

declare(strict_types=1);

namespace App\Tests\Domain\User\ValueObject\Email;

use App\Domain\User\ValueObject\Email\EmailValidationResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EmailValidationResultTest extends TestCase
{
    #[Test]
    #[DataProvider('provideIsValidCases')]
    public function isValidReturnsExpectedForEachResult(
        EmailValidationResult $result,
        bool $expected,
    ): void {
        self::assertSame($expected, $result->isValid());
    }

    /**
     * @return iterable<string, array{EmailValidationResult, bool}>
     */
    public static function provideIsValidCases(): iterable
    {
        foreach (EmailValidationResult::cases() as $result) {
            yield strtolower($result->name) => [
                $result,
                $result === EmailValidationResult::VALID,
            ];
        }
    }
}
