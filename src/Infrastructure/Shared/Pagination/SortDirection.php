<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Pagination;

enum SortDirection: string
{
    case Asc = 'asc';
    case Desc = 'desc';
}
