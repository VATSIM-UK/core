#!/usr/bin/env bash

# Runs on every devcontainer start to prepare SSH access.
# Delegates SSH configuration and authentication checks to setup-ssh.sh.

set -euo pipefail

source /workspace/.devcontainer/bin/setup-ssh.sh
