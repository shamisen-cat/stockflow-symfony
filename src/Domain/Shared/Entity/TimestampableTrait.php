<?php

declare(strict_types=1);

namespace App\Domain\Shared\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait TimestampableTrait
{
    #[ORM\Column(
        name: 'created_at',
        type: Types::DATETIME_IMMUTABLE,
    )]
    public private(set) \DateTimeImmutable $createdAt;

    #[ORM\Column(
        name: 'updated_at',
        type: Types::DATETIME_IMMUTABLE,
    )]
    public private(set) \DateTimeImmutable $updatedAt;

    protected function markCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
        $this->updatedAt = $createdAt;
    }

    protected function markUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
