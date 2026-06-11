<?php

declare(strict_types=1);

namespace App\Tests\Domain\User\ValueObject\Password;

use App\Domain\User\Exception\InvalidPlainPasswordException;
use App\Domain\User\ValueObject\Password\PlainPassword;
use App\Domain\User\ValueObject\Password\PlainPasswordValidationResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PlainPasswordTest extends TestCase
{
    #[Test]
    public function ofReturnsExpectedValueForValidPlain(): void
    {
        $plainPassword   = 'test-password';
        $plainPasswordVo = PlainPassword::of($plainPassword);

        self::assertSame($plainPassword, $plainPasswordVo->value());
    }

    #[Test]
    #[DataProvider('provideInvalidPlainCases')]
    public function ofThrowsWhenPlainIsInvalid(string $plainPassword): void
    {
        $this->expectException(InvalidPlainPasswordException::class);
        PlainPassword::of($plainPassword);
    }

    #[Test]
    #[DataProvider('providePlainValidationCases')]
    public function validateReturnsExpectedResultForEachPlain(
        string $plainPassword,
        PlainPasswordValidationResult $expected,
    ): void {
        $result = PlainPassword::validate($plainPassword);

        self::assertSame($expected, $result);
    }

    #[Test]
    public function equalsReturnsTrueWhenValuesAreEqual(): void
    {
        $plainPassword = 'test-password';
        $left          = PlainPassword::of($plainPassword);
        $right         = PlainPassword::of($plainPassword);

        self::assertTrue($left->equals($right));
    }

    #[Test]
    public function equalsReturnsFalseWhenValuesAreNotEqual(): void
    {
        $left  = PlainPassword::of('test-password');
        $right = PlainPassword::of('uest-password');

        self::assertFalse($left->equals($right));
    }

    #[Test]
    public function toStringReturnsExpectedValue(): void
    {
        $plainPassword   = 'test-password';
        $plainPasswordVo = PlainPassword::of($plainPassword);

        self::assertSame($plainPassword, (string) $plainPasswordVo);
    }

    /**
     * @return iterable<string, array{string, PlainPasswordValidationResult}>
     */
    public static function providePlainValidationCases(): iterable
    {
        yield 'valid' => [
            'test-password',
            PlainPasswordValidationResult::VALID,
        ];

        yield 'valid_min_length' => [
            str_repeat('a', PlainPassword::MIN_LENGTH),
            PlainPasswordValidationResult::VALID,
        ];

        yield 'valid_max_length' => [
            str_repeat('a', PlainPassword::MAX_LENGTH),
            PlainPasswordValidationResult::VALID,
        ];

        yield 'empty' => [
            '',
            PlainPasswordValidationResult::EMPTY,
        ];

        yield 'too_short' => [
            str_repeat('a', PlainPassword::MIN_LENGTH - 1),
            PlainPasswordValidationResult::TOO_SHORT,
        ];

        yield 'too_long' => [
            str_repeat('a', PlainPassword::MAX_LENGTH + 1),
            PlainPasswordValidationResult::TOO_LONG,
        ];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideInvalidPlainCases(): iterable
    {
        foreach (self::providePlainValidationCases() as $name => [$plainPassword, $expected]) {
            if ($expected === PlainPasswordValidationResult::VALID) {
                continue;
            }

            yield $name => [$plainPassword];
        }
    }
}
