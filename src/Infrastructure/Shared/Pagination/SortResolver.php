<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Pagination;

final class SortResolver
{
    /**
     * @param array<string, string> $sortMap URL key => DQL field
     */
    public function resolve(
        array $sortMap,
        string $sortKey,
        string $direction,
        string $defaultKey,
        SortDirection $defaultDirection = SortDirection::Asc,
    ): SortCriteria {
        if (!isset($sortMap[$defaultKey])) {
            throw new \InvalidArgumentException(
                sprintf('Default sort key "%s" is not defined in sort map.', $defaultKey),
            );
        }

        $key = $sortKey !== '' ? $sortKey : $defaultKey;

        if (!isset($sortMap[$key])) {
            $key = $defaultKey;
        }

        $field = $sortMap[$key];
        $resolvedDirection = SortDirection::tryFrom(strtolower($direction)) ?? $defaultDirection;

        return new SortCriteria($key, $field, $resolvedDirection->value);
    }
}
