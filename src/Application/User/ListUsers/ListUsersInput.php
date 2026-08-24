<?php

declare(strict_types=1);

namespace App\Application\User\ListUsers;

final readonly class ListUsersInput
{
    public const int DEFAULT_PAGE = 1;
    public const int DEFAULT_MAX_PER_PAGE = 20;
    public const int MAX_PER_PAGE = 100;

    public string $email;
    public int $page;
    public int $maxPerPage;

    public function __construct(
        string $email,
        public string $sortKey,
        public string $direction,
        int $page = self::DEFAULT_PAGE,
        int $maxPerPage = self::DEFAULT_MAX_PER_PAGE,
    ) {
        $this->email = trim($email);
        $this->page = max(1, $page);
        $this->maxPerPage = min(
            max(1, $maxPerPage),
            self::MAX_PER_PAGE,
        );
    }
}
