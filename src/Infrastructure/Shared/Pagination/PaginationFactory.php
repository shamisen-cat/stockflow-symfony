<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Pagination;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

final class PaginationFactory
{
    /**
     * @return Pagerfanta<mixed>
     */
    public function create(
        QueryBuilder $queryBuilder,
        int $page,
        int $maxPerPage,
    ): Pagerfanta {
        return Pagerfanta::createForCurrentPageWithMaxPerPage(
            adapter: new QueryAdapter($queryBuilder),
            currentPage: max(1, $page),
            maxPerPage: max(1, $maxPerPage),
        );
    }
}
