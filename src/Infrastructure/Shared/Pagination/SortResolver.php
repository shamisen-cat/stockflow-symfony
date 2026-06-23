<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Pagination;

use Symfony\Component\HttpFoundation\Request;

final class SortResolver
{
    /**
     * @param array<string, string> $map URL key => DQL field
     */
    public function resolve(
        Request $request,
        array $map,
        string $defaultKey,
        SortDirection $defaultDirection = SortDirection::Asc,
    ): SortCriteria {
        if (!isset($map[$defaultKey])) {
            throw new \InvalidArgumentException(
                sprintf('Default sort key "%s" is not defined in sort map.', $defaultKey),
            );
        }

        $key = $request->query->getString('sort', $defaultKey);

        if (!isset($map[$key])) {
            $key = $defaultKey;
        }

        $field = $map[$key];

        $directionParam = strtolower($request->query->getString('direction'));
        $direction = SortDirection::tryFrom($directionParam) ?? $defaultDirection;

        return new SortCriteria($key, $field, $direction->value);
    }
}
