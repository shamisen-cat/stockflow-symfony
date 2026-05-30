<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Password;

use App\Domain\User\Entity\User;
use App\Domain\User\Password\PlainPasswordHasherInterface;
use App\Domain\User\ValueObject\Password\HashedPassword;
use App\Domain\User\ValueObject\Password\PlainPassword;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

final readonly class PlainPasswordHasher implements PlainPasswordHasherInterface
{
    public function __construct(
        private PasswordHasherFactoryInterface $passwordHasherFactory,
    ) {
    }

    #[\Override]
    public function hash(PlainPassword $plainPassword): HashedPassword
    {
        $hasher = $this->passwordHasherFactory->getPasswordHasher(User::class);

        $hash           = $hasher->hash($plainPassword->value());
        $hashedPassword = HashedPassword::of($hash);

        return $hashedPassword;
    }
}
