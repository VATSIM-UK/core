#!/bin/bash

# -----------------------------------------------------------------------------
# Initial Composer Setup
# -----------------------------------------------------------------------------
#
#
# The script is intended to run from "postCreateCommand" in devcontainer.json.
# -----------------------------------------------------------------------------

# Stop the script if a command fails, an undefined variable is used, or a
# command within a pipeline fails.
set -euo pipefail

# -----------------------------------------------------------------------------
# Run PHP Composer Setup
# -----------------------------------------------------------------------------

cd /workspace/ && composer install
