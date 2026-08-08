<?php

declare(strict_types=1);

namespace App\Domain\User\Entity;

use App\Domain\Shared\Entity\DisablableTrait;
use App\Domain\Shared\Entity\SoftDeletableTrait;
use App\Domain\Shared\Entity\SuspendableTrait;
use App\Domain\Shared\Entity\TimestampableTrait;
use App\Domain\User\Enum\UserStatus;
use App\Domain\User\Exception\UserAlreadyDeletedException;
use App\Domain\User\Exception\UserAlreadyDisabledException;
use App\Domain\User\Exception\UserAlreadySuspendedException;
use App\Domain\User\Exception\UserDeletedException;
use App\Domain\User\Exception\UserNotDisabledException;
use App\Domain\User\Exception\UserNotSuspendedException;
use App\Domain\User\Exception\UserSuspendedException;
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
    use DisablableTrait;
    use SuspendableTrait;
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

    #[ORM\Column(
        name: 'status',
        enumType: UserStatus::class,
    )]
    public private(set) UserStatus $status;

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
            status: UserStatus::Active,
        );
    }

    private function __construct(
        Uuid $id,
        Email $email,
        HashedPassword $password,
        \DateTimeImmutable $createdAt,
        UserStatus $status,
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->status = $status;

        $this->markCreatedAt($createdAt);
    }

    public function disable(\DateTimeImmutable $disabledAt): void
    {
        if ($this->isDeleted()) {
            throw UserDeletedException::forUser($this->id);
        }

        if ($this->isSuspended()) {
            throw UserSuspendedException::forUser($this->id);
        }

        if ($this->isDisabled()) {
            throw UserAlreadyDisabledException::forUser($this->id);
        }

        $this->markDisabledAt($disabledAt);
        $this->refreshStatus();

        $this->markUpdatedAt($disabledAt);
    }

    public function enable(\DateTimeImmutable $enabledAt): void
    {
        if ($this->isDeleted()) {
            throw UserDeletedException::forUser($this->id);
        }

        if ($this->isSuspended()) {
            throw UserSuspendedException::forUser($this->id);
        }

        if (!$this->isDisabled()) {
            throw UserNotDisabledException::forUser($this->id);
        }

        $this->clearDisabledAt();
        $this->refreshStatus();

        $this->markUpdatedAt($enabledAt);
    }

    public function suspend(\DateTimeImmutable $suspendedAt): void
    {
        if ($this->isDeleted()) {
            throw UserDeletedException::forUser($this->id);
        }

        if ($this->isSuspended()) {
            throw UserAlreadySuspendedException::forUser($this->id);
        }

        $this->markSuspendedAt($suspendedAt);
        $this->refreshStatus();

        $this->markUpdatedAt($suspendedAt);
    }

    public function unsuspend(\DateTimeImmutable $unsuspendedAt): void
    {
        if ($this->isDeleted()) {
            throw UserDeletedException::forUser($this->id);
        }

        if (!$this->isSuspended()) {
            throw UserNotSuspendedException::forUser($this->id);
        }

        $this->clearSuspendedAt();
        $this->refreshStatus();

        $this->markUpdatedAt($unsuspendedAt);
    }

    public function softDelete(\DateTimeImmutable $deletedAt): void
    {
        if ($this->isDeleted()) {
            throw UserAlreadyDeletedException::forUser($this->id);
        }

        $this->markDeletedAt($deletedAt);
        $this->refreshStatus();

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

    private function refreshStatus(): void
    {
        $this->status = match (true) {
            $this->isDeleted() => UserStatus::Deleted,
            $this->isSuspended() => UserStatus::Suspended,
            $this->isDisabled() => UserStatus::Disabled,
            default => UserStatus::Active,
        };
    }
}
