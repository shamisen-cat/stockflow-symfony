<?php

declare(strict_types=1);

namespace App\Domain\Shared\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait SuspendableTrait
{
    #[ORM\Column(
        name: 'suspended_at',
        type: Types::DATETIME_IMMUTABLE,
        nullable: true,
    )]
    public private(set) ?\DateTimeImmutable $suspendedAt = null;

    protected function markSuspendedAt(\DateTimeImmutable $suspendedAt): void
    {
        $this->suspendedAt = $suspendedAt;
    }

    protected function clearSuspendedAt(): void
    {
        $this->suspendedAt = null;
    }

    public function isSuspended(): bool
    {
        return $this->suspendedAt !== null;
    }
}
