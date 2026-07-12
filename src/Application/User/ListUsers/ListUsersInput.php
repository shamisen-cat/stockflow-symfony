<?php

declare(strict_types=1);

namespace App\Application\User\ListUsers;

final readonly class ListUsersInput
{
    public const int MAX_PER_PAGE = 100;

    public function __construct(
        public string $email,
        public string $sortKey,
        public string $direction,
        public int $page,
        public int $perPage,
    ) {
    }

    public static function create(
        string $email,
        string $sortKey,
        string $direction,
        int $page,
        int $perPage,
    ): self {
        $normalizedPage = max(1, $page);

        $normalizedPerPage = min(
            max(1, $perPage),
            self::MAX_PER_PAGE,
        );

        return new self(
            email: $email,
            sortKey: $sortKey,
            direction: $direction,
            page: $normalizedPage,
            perPage: $normalizedPerPage,
        );
    }
}
