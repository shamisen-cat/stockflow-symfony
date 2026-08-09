<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Sort;

enum SortDirection: string
{
    case Asc = 'asc';
    case Desc = 'desc';
}
