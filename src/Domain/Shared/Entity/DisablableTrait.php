<?php

declare(strict_types=1);

namespace App\Domain\Shared\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait DisablableTrait
{
    #[ORM\Column(
        name: 'disabled_at',
        type: Types::DATETIME_IMMUTABLE,
        nullable: true,
    )]
    public private(set) ?\DateTimeImmutable $disabledAt = null;

    protected function markDisabledAt(\DateTimeImmutable $disabledAt): void
    {
        $this->disabledAt = $disabledAt;
    }

    protected function clearDisabledAt(): void
    {
        $this->disabledAt = null;
    }

    public function isDisabled(): bool
    {
        return $this->disabledAt !== null;
    }
}
