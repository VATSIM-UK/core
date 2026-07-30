#!/bin/bash

# -----------------------------------------------------------------------------
# Initial PHP Artisan Setup
# -----------------------------------------------------------------------------
#
#
# The script is intended to run from "postCreateCommand" in devcontainer.json.
# -----------------------------------------------------------------------------

# Stop the script if a command fails, an undefined variable is used, or a
# command within a pipeline fails.
set -euo pipefail

# -----------------------------------------------------------------------------
# Laravel application setup
# -----------------------------------------------------------------------------
# Generate the application encryption key.
#
# The APP_KEY is stored in the local .env file and is not committed to version
# control. Generating it here ensures that each development container has its
# own valid application key before Laravel starts.
# -----------------------------------------------------------------------------

# Only generate the application key if it has not been generated already
if ! grep -q '^APP_KEY=' .env; then
    php artisan key:generate
fi

# -----------------------------------------------------------------------------
# Database setup
# -----------------------------------------------------------------------------
# Recreate the CTS database from scratch, apply all Laravel migrations, and
# populate the database with the default seed data required for local
# development.
# -----------------------------------------------------------------------------

# Only progress with the database setup, if they have not been created already
if php artisan tinker --execute="exit(DB::table('core.airports')->exists() ? 0 : 1)"; then

    echo "No database tables found. Recreating the database from scratch and seeding it with default data."

    php artisan cts:migrate:fresh
    php artisan migrate

    # Seed the application's default data.
    php artisan db:seed

    # Seed additional data used only for local development and training.
    php artisan db:seed --class=Database\\Seeders\\LocalDevelopmentTrainingSeeder

else
    echo "Database tables already exist. Skipping database setup."
fi

# -----------------------------------------------------------------------------
# Frontend assets
# -----------------------------------------------------------------------------
# Build the application's frontend assets (JavaScript, CSS, etc.) for use
# within the development container.
# -----------------------------------------------------------------------------
npm run build
