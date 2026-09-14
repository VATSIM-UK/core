#!/usr/bin/env bash

set -euo pipefail

cd /workspace

step() {
    echo
    echo "==> $1"
}

sudo mkdir -p /home/vscode/.cache/composer /home/vscode/.npm /home/vscode/.config/gh
sudo chown vscode:vscode \
    /workspace/vendor \
    /workspace/node_modules \
    /home/vscode/.cache \
    /home/vscode/.cache/composer \
    /home/vscode/.config \
    /home/vscode/.config/gh \
    /home/vscode/.npm

git config --global --add safe.directory /workspace

if [ ! -f .env ]; then
    step "Creating .env"
    cp .env.example .env
else
    step ".env already exists - leaving it untouched"

    if ! grep -qE '^DB_MYSQL_HOST=mysql$' .env; then
        echo "warning: your .env does not use the container database."
        echo "         Set DB_MYSQL_HOST=mysql (and REDIS_HOST=redis, REDIS_PASSWORD=root)"
        echo "         or delete .env and re-run this script."
    fi
fi

if gh auth status >/dev/null 2>&1; then
    step "Handing your GitHub token to Composer (avoids API rate limits)"
    composer config -g github-oauth.github.com "$(gh auth token)" || true
else
    step "Skipping Composer GitHub token - run 'gh auth login' then re-run this script if Composer hits rate limits"
fi

step "Installing Composer dependencies"
composer install --no-interaction

if grep -qE '^APP_KEY=$' .env; then
    step "Generating the application key"
    php artisan key:generate
fi

step "Installing npm dependencies and building assets"
npm ci
npm run build

step "Creating the mock CTS database"
php artisan cts:migrate:fresh

step "Running migrations (drops and rebuilds the core database)"
php artisan migrate:fresh --force

step "Seeding roles and permissions"
php artisan db:seed --force

step "Granting the VATSIM sandbox accounts admin access"
php artisan db:seed --force --class='Database\Seeders\LocalDevelopment\SandboxAccountsSeeder'

step "Installing git hooks"
prek install || true
if [ ! -x .git/hooks/pre-commit ]; then
    echo "warning: git hooks were not installed; run 'prek install' by hand."
fi

cat <<'BANNER'

The app is being served at http://core.test

In the container:

    make frontend-dev    # Vite, for live asset rebuilds
    php artisan test     # test suite

Log in at http://core.test with any VATSIM sandbox account
(https://vatsim.dev/services/connect/sandbox) - all of them are already admins.

Run 'gh auth login' once if you want the GitHub CLI.

BANNER
