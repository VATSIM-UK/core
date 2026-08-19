#!/usr/bin/env bash

# Perform the initial Node.js setup for the development container.

set -euo pipefail

# Install the Node.js dependencies for the application.
npm ci #--include=dev

# Build the frontend assets.
npm run build
