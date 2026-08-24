<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Uuid;

use App\Domain\Shared\Uuid\UuidGeneratorInterface;
use Symfony\Component\Uid\Uuid;

final readonly class UuidGenerator implements UuidGeneratorInterface
{
    #[\Override]
    public function generate(): Uuid
    {
        return Uuid::v7();
    }
}
