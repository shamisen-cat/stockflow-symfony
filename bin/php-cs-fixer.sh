#!/bin/zsh
PROJECT_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$PROJECT_ROOT" || exit 1
./vendor/bin/php-cs-fixer "$@"
