<?php

declare(strict_types=1);

namespace App\Tests\Application\User\ListUsers;

use App\Application\User\ListUsers\ListUsersInput;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ListUsersInputTest extends TestCase
{
    #[Test]
    public function constructorTrimsEmail(): void
    {
        $input = new ListUsersInput(
            email: '  test@example.com  ',
            sortKey: 'email',
            direction: 'asc',
        );

        self::assertSame('test@example.com', $input->email);
        self::assertSame('email', $input->sortKey);
        self::assertSame('asc', $input->direction);
        self::assertSame(ListUsersInput::DEFAULT_PAGE, $input->page);
        self::assertSame(ListUsersInput::DEFAULT_MAX_PER_PAGE, $input->maxPerPage);
    }

    #[Test]
    public function constructorClampsPageToMinimum(): void
    {
        $input = new ListUsersInput(
            email: '',
            sortKey: '',
            direction: '',
            page: 0,
        );

        self::assertSame(1, $input->page);
    }

    #[Test]
    public function constructorClampsMaxPerPageToMinimum(): void
    {
        $input = new ListUsersInput(
            email: '',
            sortKey: '',
            direction: '',
            maxPerPage: 0,
        );

        self::assertSame(1, $input->maxPerPage);
    }

    #[Test]
    public function constructorClampsMaxPerPageToMaximum(): void
    {
        $input = new ListUsersInput(
            email: '',
            sortKey: '',
            direction: '',
            maxPerPage: ListUsersInput::MAX_PER_PAGE + 1,
        );

        self::assertSame(ListUsersInput::MAX_PER_PAGE, $input->maxPerPage);
    }
}
