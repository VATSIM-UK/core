#!/usr/bin/env bash

# Perform the initial Laravel setup for the development container.
# Intended to run from the devcontainer's postCreateCommand.

set -euo pipefail

# Generate an application key if one has not already been configured.
if ! grep -q '^APP_KEY=' .env; then
    php artisan key:generate
fi

# Initialise the database if it has not already been populated.
if php artisan tinker --execute="exit(DB::table('core.airports')->exists() ? 0 : 1)"; then

    echo "No database tables found. Recreating the database and seeding default data."

    php artisan cts:migrate:fresh
    php artisan migrate

    # Seed the application's default and local development data.
    php artisan db:seed
    php artisan db:seed --class=Database\\Seeders\\LocalDevelopmentTrainingSeeder

else
    echo "Database tables already exist. Skipping database setup."
fi

# Build the frontend assets.
npm run build
