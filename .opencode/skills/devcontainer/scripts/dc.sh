#!/usr/bin/env bash
# Run a command inside this repo's devcontainer.
# Usage: dc.sh '<command>'   (stdin is forwarded, so heredocs work)
# Overrides: DEVCONTAINER_CONTAINER, DEVCONTAINER_USER, DEVCONTAINER_WORKSPACE.

# Stop MSYS/Git-Bash from rewriting /workspace into a Windows path.
export MSYS_NO_PATHCONV=1
export MSYS2_ARG_CONV_EXCL='*'

set -uo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

if [[ $# -eq 0 ]]; then
	echo "usage: dc.sh '<command>'" >&2
	exit 64
fi

container="$(bash "$here/find-devcontainer.sh")" || exit $?

exec docker exec \
	-u "${DEVCONTAINER_USER:-vscode}" \
	-w "${DEVCONTAINER_WORKSPACE:-/workspace}" \
	-i "$container" \
	bash -lc "$*"
