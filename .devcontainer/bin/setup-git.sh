#!/bin/bash

# -----------------------------------------------------------------------------
# Initial Git Setup
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

# -----------------------------------------------------------------------------
# Validate the required environment variables
# -----------------------------------------------------------------------------
#
# The script should not erase the existing Git configuration unless replacement
# values are available.
if [[ -z "${GIT_USER_NAME:-}" || -z "${GIT_USER_EMAIL:-}" ]]; then
    echo "Git configuration was not changed."
    echo "GIT_USER_NAME and GIT_USER_EMAIL must both be provided."
    exit 1
fi

# -----------------------------------------------------------------------------
# Remove the existing global Git identity
# -----------------------------------------------------------------------------
#
# "--unset-all" removes every global value stored for the specified setting.
#
# The command may return a non-zero exit code when the setting does not exist.
# "|| true" prevents that harmless result from stopping the script.
git config --global --unset-all user.name || true
git config --global --unset-all user.email || true

# -----------------------------------------------------------------------------
# Set the Git identity from the environment variables
# -----------------------------------------------------------------------------
git config --global user.name "$GIT_USER_NAME"
git config --global user.email "$GIT_USER_EMAIL"

# Create an empty bashrc file if it does not exist. This prevents errors when the container is started
# with a non-root user and the user's home directory does not contain a .bashrc file
GIT_FILE="$HOME/setup-git-info.sh"
cat /dev/null > "$GIT_FILE"

# Display the resulting configuration so the developer can confirm it worked.
echo 'echo "🐳 Image: $(. /etc/os-release && echo "$PRETTY_NAME")"' >> "$GIT_FILE"
echo "echo \"📂 PHP version Installed: $(php -v | head -n 1) \"" >> "$GIT_FILE"
echo "echo \"💻 Git Identity: $(git config --global user.name) ($(git config --global user.email)) \"" >> "$GIT_FILE"
