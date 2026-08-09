<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Sort;

final readonly class SortCriteria
{
    public function __construct(
        public string $key,
        public string $field,
        public string $direction,
    ) {
    }
}
