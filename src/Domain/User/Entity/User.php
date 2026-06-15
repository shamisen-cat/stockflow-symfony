<?php

declare(strict_types=1);

namespace App\Domain\User\Entity;

use App\Domain\Shared\Entity\SoftDeletableTrait;
use App\Domain\Shared\Entity\TimestampableTrait;
use App\Domain\User\Exception\UserAlreadyDeletedException;
use App\Domain\User\ValueObject\Email\Email;
use App\Domain\User\ValueObject\Password\HashedPassword;
use App\Infrastructure\User\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
#[ORM\UniqueConstraint(
    name: 'uniq_user_email_active',
    columns: ['email'],
    options: ['where' => 'deleted_at IS NULL'],
)]
final class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use SoftDeletableTrait;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\Column(
        name: 'id',
        type: UuidType::NAME,
    )]
    public private(set) Uuid $id;

    #[ORM\Embedded(
        class: Email::class,
        columnPrefix: false,
    )]
    public private(set) Email $email;

    #[ORM\Embedded(
        class: HashedPassword::class,
        columnPrefix: false,
    )]
    public private(set) HashedPassword $password;

    public static function create(
        Uuid $id,
        Email $email,
        HashedPassword $password,
        \DateTimeImmutable $createdAt,
    ): self {
        return new self(
            id: $id,
            email: $email,
            password: $password,
            createdAt: $createdAt,
        );
    }

    private function __construct(
        Uuid $id,
        Email $email,
        HashedPassword $password,
        \DateTimeImmutable $createdAt,
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;

        $this->markCreatedAt($createdAt);
    }

    public function softDelete(\DateTimeImmutable $deletedAt): void
    {
        if ($this->isDeleted()) {
            throw UserAlreadyDeletedException::forUser($this->id);
        }

        $this->markDeletedAt($deletedAt);
        $this->markUpdatedAt($deletedAt);
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
        return $this->password->value();
    }

    /**
     * @see UserInterface
     */
    #[\Override]
    #[\Deprecated('since Symfony 7.3, erase credentials using the "__serialize()" method instead')]
    public function eraseCredentials(): void
    {
    }
}
