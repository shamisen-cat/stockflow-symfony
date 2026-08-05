<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Symfony\Component\Security\Core\User\UserInterface;

final class UnsupportedUser implements UserInterface
{
    #[\Override]
    public function getUserIdentifier(): string
    {
        return 'unsupported-user';
    }

    #[\Override]
    public function getRoles(): array
    {
        return [];
    }

    #[\Override]
    public function eraseCredentials(): void
    {
    }
}
