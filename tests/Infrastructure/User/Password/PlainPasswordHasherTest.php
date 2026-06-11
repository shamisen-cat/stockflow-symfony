<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\User\Password;

use App\Domain\User\Password\PlainPasswordHasherInterface;
use App\Domain\User\ValueObject\Password\PlainPassword;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PlainPasswordHasherTest extends KernelTestCase
{
    #[Test]
    public function hashReturnsArgon2idHashedPassword(): void
    {
        self::bootKernel();

        $plainPasswordHasher = self::getContainer()->get(PlainPasswordHasherInterface::class);

        self::assertInstanceOf(PlainPasswordHasherInterface::class, $plainPasswordHasher);

        $plainPassword  = PlainPassword::of('test-password');
        $hashedPassword = $plainPasswordHasher->hash($plainPassword);

        self::assertStringStartsWith('$argon2id$', $hashedPassword->value());
    }
}
