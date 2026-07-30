#!/usr/bin/env bash

# Configure the Git identity for the devcontainer using the values provided by
# GIT_USER_NAME and GIT_USER_EMAIL. Intended to run from postCreateCommand.

set -euo pipefail

# Both values are required before updating the Git configuration.
if [[ -z "${GIT_USER_NAME:-}" || -z "${GIT_USER_EMAIL:-}" ]]; then
    echo "Git configuration was not changed."
    echo "GIT_USER_NAME and GIT_USER_EMAIL must both be provided."
    exit 1
fi

# Remove any existing global Git identity.
git config --global --unset-all user.name || true
git config --global --unset-all user.email || true

# Configure Git with the supplied identity.
git config --global user.name "$GIT_USER_NAME"
git config --global user.email "$GIT_USER_EMAIL"

# Generate a script that displays environment information when sourced.
GIT_FILE="$HOME/setup-git-info.sh"
cat /dev/null > "$GIT_FILE"

echo 'echo "🐳 Image: $(. /etc/os-release && echo "$PRETTY_NAME")"' >> "$GIT_FILE"
echo "echo \"📂 PHP version: $(php -v | head -n 1)\"" >> "$GIT_FILE"
echo "echo \"💻 Git identity: $(git config --global user.name) ($(git config --global user.email))\"" >> "$GIT_FILE"
