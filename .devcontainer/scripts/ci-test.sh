#!/usr/bin/env bash
set -euo pipefail

cd /workspace

sudo mkdir -p /home/vscode/.cache/composer /home/vscode/.npm
sudo chown -R vscode:vscode \
    /workspace/vendor \
    /workspace/node_modules \
    /home/vscode/.cache \
    /home/vscode/.npm

composer config -g github-oauth.github.com "${GITHUB_TOKEN:-}"

[ -f .env ] || cp .env.example .env
php artisan key:generate --force

composer install --no-interaction
npm ci
npm run build

php artisan test --parallel --processes=4 --recreate-databases
