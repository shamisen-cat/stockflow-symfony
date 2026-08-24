<?php

declare(strict_types=1);

namespace App\Domain\Shared\Uuid;

use Symfony\Component\Uid\Uuid;

interface UuidGeneratorInterface
{
    public function generate(): Uuid;
}
