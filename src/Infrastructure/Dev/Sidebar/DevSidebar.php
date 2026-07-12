<?php

declare(strict_types=1);

namespace App\Infrastructure\Dev\Sidebar;

final readonly class DevSidebar
{
    /**
     * @param list<DevSidebarLink> $links
     * @param list<DevSidebarLink> $subLinks
     */
    public function __construct(
        public array $links,
        public array $subLinks,
        public DevSidebarLinkId $currentLink,
    ) {
    }
}
