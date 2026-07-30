#!/usr/bin/env bash

# -----------------------------------------------------------------------------
# Initial Development Container Setup
# -----------------------------------------------------------------------------
# The script is intended to run from "postCreateCommand" in devcontainer.json.
# -----------------------------------------------------------------------------

# Stop the script if a command fails, an undefined variable is used, or a
# command within a pipeline fails.
set -euo pipefail

# Move our bashrc file to the home directory of the user that is running the container.
cp /workspace/.devcontainer/bin/bashrc.sh "$HOME/.bashrc"

# Run the shared GIT setup script to configure the Git identity.
bash "/workspace/.devcontainer/bin/setup-git.sh"

# Run the shared SSH setup script to configure permissions and test GitHub authentication.
bash "/workspace/.devcontainer/bin/setup-ssh.sh"

# Install the Composer dependencies for the project.
bash "/workspace/.devcontainer/bin/setup-composer.sh"

# Build and see the database - if not already there.
bash "/workspace/.devcontainer/bin/setup-artisan.sh"

# Install the Prek Git hooks for the project.
bash "/workspace/.devcontainer/bin/setup-prek.sh"
