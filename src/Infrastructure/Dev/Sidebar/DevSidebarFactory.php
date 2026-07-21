<?php

declare(strict_types=1);

namespace App\Infrastructure\Dev\Sidebar;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class DevSidebarFactory
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function create(DevSidebarLinkId $currentLink): DevSidebar
    {
        $links = [
            new DevSidebarLink(
                id: DevSidebarLinkId::User,
                label: 'dev.user.sidebar',
                href: $this->urlGenerator->generate('app_dev_users'),
                icon: 'lucide:users',
            ),
            new DevSidebarLink(
                id: DevSidebarLinkId::Organization,
                label: 'dev.organization.sidebar',
                href: '#',
                icon: 'lucide:building',
            ),
            new DevSidebarLink(
                id: DevSidebarLinkId::Membership,
                label: 'dev.membership.sidebar',
                href: '#',
                icon: 'lucide:id-card',
            ),
        ];

        $subLinks = match ($currentLink) {
            DevSidebarLinkId::User => [
                new DevSidebarLink(
                    id: DevSidebarLinkId::UserCreate,
                    label: 'dev.sidebar.create',
                    href: $this->urlGenerator->generate('app_dev_users_new'),
                    icon: 'lucide:plus',
                ),
            ],
            DevSidebarLinkId::Organization => [
                new DevSidebarLink(
                    id: DevSidebarLinkId::OrganizationCreate,
                    label: 'dev.sidebar.create',
                    href: '#',
                    icon: 'lucide:plus',
                ),
            ],
            default => [],
        };

        return new DevSidebar($links, $subLinks, $currentLink);
    }
}
