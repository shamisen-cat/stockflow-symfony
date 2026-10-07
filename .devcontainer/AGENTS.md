# Stockflow

This project is a Symfony application running on [FrankenPHP](https://frankenphp.dev), generated using [Symfony Docker](https://github.com/dunglas/symfony-docker).

The Dockerfile uses a multi-stage build with separate development (dev) and production (prod) targets.

## Stack

- FrankenPHP with Caddy
- [Mercure](https://mercure.rocks) for real-time
- [Vulcain](https://vulcain.rocks) for preloading
- PostgreSQL

App setup steps are in `README.md`.

## Dev Container Environment

This project runs inside a Dev Container with an outbound firewall that blocks all traffic except explicitly allowed domains.

## Whitelisting a Domain

If an outbound request fails (e.g., `curl`, `composer require`, `npm install` to a new registry), the domain may need to be added to the firewall allowlist.

Edit `.devcontainer/init-firewall.sh` and add the domain to the `ipset=` line in the dnsmasq configuration block:

```bash
ipset=/github.com/anthropic.com/.../NEW_DOMAIN.COM/allowed-domains
```

Then rebuild the Dev Container to apply the change.

## Language

- English for code, comments, documentation, and user-facing strings
- Prefer natural phrasing over literal translation from Japanese
- Japanese only for commit messages and pull requests (see `.cursor/rules/`)

## Cursor Rules

Project-specific agent instructions live in `.cursor/rules/` (commits, pull requests, language).
