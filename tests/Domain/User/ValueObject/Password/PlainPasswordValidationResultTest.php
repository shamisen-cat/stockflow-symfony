<?php

declare(strict_types=1);

namespace App\Tests\Domain\User\ValueObject\Password;

use App\Domain\User\ValueObject\Password\PlainPasswordValidationResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PlainPasswordValidationResultTest extends TestCase
{
    #[Test]
    #[DataProvider('provideIsValidCases')]
    public function isValidReturnsExpectedForEachResult(
        PlainPasswordValidationResult $result,
        bool $expected,
    ): void {
        self::assertSame($expected, $result->isValid());
    }

    /**
     * @return iterable<string, array{PlainPasswordValidationResult, bool}>
     */
    public static function provideIsValidCases(): iterable
    {
        foreach (PlainPasswordValidationResult::cases() as $result) {
            yield strtolower($result->name) => [
                $result,
                $result === PlainPasswordValidationResult::VALID,
            ];
        }
    }
}
