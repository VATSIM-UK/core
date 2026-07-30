#!/bin/bash

# -----------------------------------------------------------------------------
# Setup SSH Keys and Permissions
# -----------------------------------------------------------------------------
#
# This script is shared between the postStartCommand and preStartCommand.
#
# The host's ~/.ssh directory is bind-mounted into the container as
# /root/.ssh. SSH requires strict permissions on the directory and private key
# files; otherwise it refuses to use them, resulting in errors such as:
#
#   Permissions 0777 for '/root/.ssh/id_ed25519' are too open.
#
# These permissions are applied on every container start to ensure Git and
# Composer can authenticate with GitHub using the mounted SSH key.
# -----------------------------------------------------------------------------

set -euo pipefail

# Ensure the SSH directory is only accessible by the owner.
chmod 700 /root/.ssh

# Private keys and SSH configuration must not be readable by other users.
chmod 600 /root/.ssh/id_ed25519
chmod 600 /root/.ssh/config

# Public keys and the known_hosts file may be world-readable.
[ -f /root/.ssh/id_ed25519.pub ] && chmod 644 /root/.ssh/id_ed25519.pub
[ -f /root/.ssh/known_hosts ] && chmod 644 /root/.ssh/known_hosts

# GitHub prints its successful SSH authentication message to stderr and returns
# a non-zero exit status because it does not provide interactive shell access.
# Capture both stdout and stderr, and prevent the expected non-zero status from
# terminating this script.
github_ssh_output="$( ssh -o BatchMode=yes -o ConnectTimeout=10 -T git@github.com 2>&1 || true)"

# Create a an empty status file
cat /dev/null > /root/.github-ssh-status

if echo "$github_ssh_output" | grep -q "successfully authenticated"; then
    echo "✅ GitHub SSH authentication is working." > /root/.github-ssh-status
else
    echo "⛔ GitHub SSH authentication failed." > /root/.github-ssh-status
fi
