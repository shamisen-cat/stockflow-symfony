<?php

declare(strict_types=1);

namespace App\Tests\Domain\User\ValueObject\Password;

use App\Domain\User\Exception\InvalidHashedPasswordException;
use App\Domain\User\ValueObject\Password\HashedPassword;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HashedPasswordTest extends TestCase
{
    #[Test]
    public function ofReturnsExpectedValueForValidHash(): void
    {
        $hash           = '$argon2id$v=19$m=65536,t=4,p=1$dummy-argon2id-hash';
        $hashedPassword = HashedPassword::of($hash);

        self::assertSame($hash, $hashedPassword->value());
    }

    #[Test]
    public function ofThrowsWhenHashIsEmpty(): void
    {
        try {
            HashedPassword::of('');
            self::fail('Expected InvalidHashedPasswordException was not thrown.');
        } catch (InvalidHashedPasswordException $e) {
            self::assertSame('Hashed password must not be empty.', $e->getMessage());
            self::assertNull($e->algorithm);
        }
    }

    #[Test]
    public function ofThrowsWhenHashIsTooLong(): void
    {
        try {
            HashedPassword::of(str_repeat('a', HashedPassword::MAX_LENGTH + 1));
            self::fail('Expected InvalidHashedPasswordException was not thrown.');
        } catch (InvalidHashedPasswordException $e) {
            self::assertSame(
                sprintf(
                    'Hashed password must not exceed %d characters.',
                    HashedPassword::MAX_LENGTH,
                ),
                $e->getMessage(),
            );
            self::assertNull($e->algorithm);
        }
    }

    #[Test]
    #[DataProvider('provideNonArgon2idHashCases')]
    public function ofThrowsWhenHashIsNotArgon2id(string $hash, string $expectedAlgorithm): void
    {
        try {
            HashedPassword::of($hash);
            self::fail('Expected InvalidHashedPasswordException was not thrown.');
        } catch (InvalidHashedPasswordException $e) {
            self::assertSame('Hashed password must use Argon2id.', $e->getMessage());
            self::assertSame($expectedAlgorithm, $e->algorithm);
        }
    }

    #[Test]
    public function equalsReturnsTrueWhenValuesAreEqual(): void
    {
        $hash  = '$argon2id$v=19$m=65536,t=4,p=1$dummy-argon2id-hash';
        $left  = HashedPassword::of($hash);
        $right = HashedPassword::of($hash);

        self::assertTrue($left->equals($right));
    }

    #[Test]
    public function equalsReturnsFalseWhenValuesAreNotEqual(): void
    {
        $left  = HashedPassword::of('$argon2id$v=19$m=65536,t=4,p=1$dummy-argon2id-hash');
        $right = HashedPassword::of('$argon2id$v=19$m=65536,t=4,p=1$dummy-argon2id-hasi');

        self::assertFalse($left->equals($right));
    }

    #[Test]
    public function toStringReturnsExpectedValue(): void
    {
        $hash           = '$argon2id$v=19$m=65536,t=4,p=1$dummy-argon2id-hash';
        $hashedPassword = HashedPassword::of($hash);

        self::assertSame($hash, (string) $hashedPassword);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideNonArgon2idHashCases(): iterable
    {
        yield 'bcrypt' => [
            '$2y$10$dummy-bcrypt-hash-00000000000000000000000000000000000',
            'bcrypt',
        ];

        yield 'invalid_algorithm' => [
            '$2y$10$dummy-bcrypt-hash',
            'unknown',
        ];

        yield 'plain_password' => [
            'dummy-plain-password',
            'unknown',
        ];
    }
}
