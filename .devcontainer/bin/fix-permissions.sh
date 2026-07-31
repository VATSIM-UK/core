#!/usr/bin/env bash
set -euo pipefail

# Fix permissions for Laravel storage and cache directories

chown -R application:application /workspace/storage /workspace/bootstrap/cache
chmod -R u+rwX,g+rwX /workspace/storage /workspace/bootstrap/cache
