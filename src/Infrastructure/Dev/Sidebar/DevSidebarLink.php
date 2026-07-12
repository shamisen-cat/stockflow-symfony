<?php

declare(strict_types=1);

namespace App\Infrastructure\Dev\Sidebar;

final readonly class DevSidebarLink
{
    public function __construct(
        public DevSidebarLinkId $id,
        public string $label,
        public string $href,
        public string $icon,
    ) {
    }
}
