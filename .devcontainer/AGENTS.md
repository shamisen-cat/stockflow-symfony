# Stockflow

This project is a Symfony application running on [FrankenPHP](https://frankenphp.dev), generated using [Symfony Docker](https://github.com/dunglas/symfony-docker).

The Dockerfile uses a multi-stage build with separate development (dev) and production (prod) targets.

## General

- Respond in Japanese
- When adding or ordering items, follow established conventions; if none exist, use a consistent order (e.g., alphabetical) and note why when introducing a new order

## Language

- English for code, comments, documentation, and user-facing strings
- Prefer natural phrasing over literal translation from Japanese
- Japanese only for commit messages and pull requests (see `git` and `pull-requests` in `.cursor/rules/`)

## Conventions

- Follow `.markdownlint.yaml` for Markdown
- Add English words flagged by cspell to `words` in `cspell.json`

## Tech Stack

- PHP 8.4 (`~8.4.0` in `composer.json`)
- Symfony 7.4
- FrankenPHP with Caddy (Mercure, Vulcain)
- PostgreSQL 18 with Doctrine ORM
- PHP-CS-Fixer for code style
- PHPStan for static analysis
- PHPUnit for testing
- Dev Container (Docker Compose with FrankenPHP / Caddy and PostgreSQL)

## Architecture

See `architecture` in `.cursor/rules/`

## Development Environment

- Coding agents run inside the `php` (FrankenPHP) container; run commands directly rather than through `docker compose exec`
- App URL: `https://localhost` from the host browser (ports 80/443)
- App setup steps are in `README.md`

## Database

See `database` in `.cursor/rules/`

## Environment Variables and Secrets

- `.env` is committed and holds only placeholder or default values; real values go in `.env.local` or `.env.*.local`, which are not committed
- When adding an environment variable, also add it to `.env` with a placeholder value
- Reading `.env.local` or `.env.*.local` files is blocked; ask the user if their values are needed

## Outbound Firewall

If outbound requests fail, see `outbound-firewall` in `.cursor/rules/`
