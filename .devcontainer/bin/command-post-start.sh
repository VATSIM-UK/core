#!/bin/bash

# -----------------------------------------------------------------------------
# Development Container Initialisation
# -----------------------------------------------------------------------------
#
# This script is executed by the devcontainer's `postStartCommand` each time
# the container starts.
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

# Run the shared SSH setup script to configure permissions and test GitHub authentication.
source /workspace/.devcontainer/bin/setup-ssh.sh
