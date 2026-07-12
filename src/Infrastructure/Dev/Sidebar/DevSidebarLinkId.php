<?php

declare(strict_types=1);

namespace App\Infrastructure\Dev\Sidebar;

enum DevSidebarLinkId: string
{
    case User = 'user';
    case UserCreate = 'user_create';

    case Organization = 'organization';
    case OrganizationCreate = 'organization_create';

    case Membership = 'membership';
}
