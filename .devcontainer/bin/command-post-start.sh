#!/usr/bin/env bash

# Runs on every devcontainer start

set -euo pipefail

# Prepare SSH access.
source /workspace/.devcontainer/bin/setup-ssh.sh

# Ensure the correct permissions for Laravel storage and cache directories
source /workspace/.devcontainer/bin/fix-permissions.sh
