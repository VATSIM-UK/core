#!/usr/bin/env bash

# Ensure the mounted SSH keys have the permissions required by OpenSSH.
# Also verifies GitHub SSH authentication and records the result for later use.

set -euo pipefail

# Restrict access to the SSH directory and private key material.
chmod 700 /root/.ssh
chmod 600 /root/.ssh/id_ed25519
chmod 600 /root/.ssh/config

# Public keys and known_hosts may be world-readable.
[ -f /root/.ssh/id_ed25519.pub ] && chmod 644 /root/.ssh/id_ed25519.pub
[ -f /root/.ssh/known_hosts ] && chmod 644 /root/.ssh/known_hosts

# GitHub returns a non-zero exit code after successful authentication because
# shell access is not provided, so ignore the exit status and inspect the output.
github_ssh_output="$(ssh -o BatchMode=yes -o ConnectTimeout=10 -T git@github.com 2>&1 || true)"

# Record the authentication status for use by other scripts.
cat /dev/null > /root/.github-ssh-status

if echo "$github_ssh_output" | grep -q "successfully authenticated"; then
    echo "✅ GitHub SSH authentication is working." > /root/.github-ssh-status
else
    echo "⛔ GitHub SSH authentication failed." > /root/.github-ssh-status
fi
