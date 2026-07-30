#!/bin/bash

# -----------------------------------------------------------------------------
# Initial Development Container Setup
# -----------------------------------------------------------------------------
#
# VS Code may copy the Git configuration from the host computer into the Dev
# Container. This can include the host user's Git name and email address.
#
# This script removes those copied values and replaces them with the values
# supplied through the container environment variables:
#
#   GIT_USER_NAME
#   GIT_USER_EMAIL
#
# The script is intended to run from "postCreateCommand" in devcontainer.json.
# -----------------------------------------------------------------------------

# Stop the script if a command fails, an undefined variable is used, or a
# command within a pipeline fails.
set -euo pipefail

# Move our bashrc file to the home directory of the user that is running the container.
# This ensures that the bashrc file is in the correct location for the user.
# This helps with creating a consistent start up message and environment for the user
# when they open a new terminal in the container.
cp /workspace/.devcontainer/bin/bashrc.sh "$HOME/.bashrc"

# Run the shared GIT setup script to configure the Git identity.
# This avoids
# - accidentally using the host machine's Git identity in the container, and
# - the need to configure Git identity in every project that is created in the container.
bash "/workspace/.devcontainer/bin/setup-git.sh"

# Run the shared SSH setup script to configure permissions
# and test GitHub authentication.
bash "/workspace/.devcontainer/bin/setup-ssh.sh"

# Install the Composer dependencies for the project.
# This is done here to ensure that the dependencies are installed in the container
# and not on the host machine. This is important because the dependencies may
# be different for the container and the host machine, and we want to ensure that
# the dependencies are installed in the container so that the project can run
# correctly in the container.
bash "/workspace/.devcontainer/bin/setup-composer.sh"

# Run the artisan command to create the database-schema and seed the database with initial data.
# This is done here to ensure that the database is created and seeded in the container
# and not on the host machine. This is important because the database may be different
# for the container and the host machine, and we want to ensure that the database is created and seeded in the container so that the project can run correctly in the container.
bash "/workspace/.devcontainer/bin/setup-artisan.sh"

# Install the Prek Git hooks for the project.
# This is done here to ensure that the hooks are installed in the container
# and not on the host machine. This is important because the hooks may be different
# for the container and the host machine, and we want to ensure that the hooks are installed in the container so that the project can run correctly in the container.
bash "/workspace/.devcontainer/bin/setup-prek.sh"
