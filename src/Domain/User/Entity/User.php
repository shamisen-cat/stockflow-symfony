<?php

declare(strict_types=1);

namespace App\Domain\User\Entity;

use App\Infrastructure\User\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
final class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(
        name: 'id',
        type: UuidType::NAME,
    )]
    public private(set) Uuid $id;

    #[ORM\Column(
        name: 'email',
        type: Types::STRING,
        length: 255,
        unique: true,
    )]
    public private(set) string $email;

    public function __construct(
        Uuid $id,
        string $email,
    ) {
        $this->id    = $id;
        $this->email = $email;
    }

    /**
     * @see UserInterface
     */
    #[\Override]
    public function getUserIdentifier(): string
    {
        return $this->id->toRfc4122();
    }

    /**
     * @see UserInterface
     */
    #[\Override]
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    #[\Override]
    public function getPassword(): string
    {
        return '';
    }

    /**
     * @see UserInterface
     */
    #[\Deprecated('since Symfony 7.3, erase credentials using the "__serialize()" method instead')]
    #[\Override]
    public function eraseCredentials(): void
    {
    }
}
