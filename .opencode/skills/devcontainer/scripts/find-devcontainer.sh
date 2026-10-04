#!/usr/bin/env bash
# Print the running container name that holds this repo's devcontainer.
# Exits non-zero with the candidates on stderr if it cannot decide.
# Override with DEVCONTAINER_CONTAINER=<name>.

# Stop MSYS/Git-Bash from rewriting /workspace into a Windows path.
export MSYS_NO_PATHCONV=1
export MSYS2_ARG_CONV_EXCL='*'

set -uo pipefail

if ! command -v docker >/dev/null 2>&1; then
	echo "find-devcontainer: docker not found on PATH" >&2
	exit 1
fi

if [[ -n "${DEVCONTAINER_CONTAINER:-}" ]]; then
	if docker ps --format '{{.Names}}' | grep -Fxq "$DEVCONTAINER_CONTAINER"; then
		echo "$DEVCONTAINER_CONTAINER"
		exit 0
	fi
	echo "find-devcontainer: DEVCONTAINER_CONTAINER='$DEVCONTAINER_CONTAINER' is not running" >&2
	exit 3
fi

running="$(docker ps --format '{{.Names}}')"
if [[ -z "$running" ]]; then
	echo "find-devcontainer: no running containers" >&2
	exit 1
fi

label() {
	docker inspect -f "{{ index .Config.Labels \"$2\" }}" "$1" 2>/dev/null || true
}

real_label() {
	local v
	v="$(label "$1" "$2")"
	if [[ -n "$v" && "$v" != "<no value>" ]]; then
		printf '%s' "$v"
	fi
}

slug() {
	printf '%s' "$1" | tr '\\' '/' | sed 's:/*$::' | awk -F/ '{print $NF}'
}

cache="${TMPDIR:-/tmp}/opencode-devcontainer-$(basename "$PWD")"

# Devcontainer containers carry devcontainer.* labels; sidecars do not.
candidates=()
while IFS= read -r c; do
	[[ -z "$c" ]] && continue
	cfg="$(real_label "$c" "devcontainer.config_file")"
	folder="$(real_label "$c" "devcontainer.local_folder")"
	if [[ -n "$cfg" || -n "$folder" ]]; then
		candidates+=("$c")
	fi
done <<<"$running"

# Fallback: compose service named "app" (still excludes mysql/redis/pma).
if [[ ${#candidates[@]} -eq 0 ]]; then
	while IFS= read -r c; do
		[[ -z "$c" ]] && continue
		[[ "$(label "$c" "com.docker.compose.service")" == "app" ]] && candidates+=("$c")
	done <<<"$running"
fi

if [[ ${#candidates[@]} -eq 0 ]]; then
	echo "find-devcontainer: no devcontainer-like running container found." >&2
	echo "Running containers:" >&2
	echo "$running" | sed 's/^/  - /' >&2
	exit 2
fi

if [[ ${#candidates[@]} -eq 1 ]]; then
	printf '%s\n' "${candidates[0]}"
	printf '%s\n' "${candidates[0]}" >"$cache" 2>/dev/null || true
	exit 0
fi

if [[ -f "$cache" ]]; then
	cached="$(cat "$cache")"
	for c in "${candidates[@]}"; do
		if [[ "$c" == "$cached" ]]; then
			echo "$c"
			exit 0
		fi
	done
fi

# Match by in-container git remote, falling back to the folder-name label.
host_slug="$(basename "$PWD")"
host_remote="$(git -C "$PWD" config --get remote.origin.url 2>/dev/null || true)"

matches=()
for c in "${candidates[@]}"; do
	folder="$(real_label "$c" "devcontainer.local_folder")"
	cfg="$(real_label "$c" "devcontainer.config_file")"
	if [[ -n "$folder" ]]; then
		label_slug="$(slug "$folder")"
	elif [[ -n "$cfg" ]]; then
		label_slug="$(slug "$(dirname "$cfg")")"
	else
		label_slug=""
	fi

	c_remote="$(docker exec -u "${DEVCONTAINER_USER:-vscode}" "$c" \
		bash -lc 'git -C /workspace config --get remote.origin.url' 2>/dev/null || true)"

	if [[ -n "$host_remote" && "$c_remote" == "$host_remote" ]]; then
		matches+=("$c")
	elif [[ -n "$host_slug" && "$label_slug" == "$host_slug" ]]; then
		matches+=("$c")
	fi
done

if [[ ${#matches[@]} -eq 1 ]]; then
	printf '%s\n' "${matches[0]}"
	printf '%s\n' "${matches[0]}" >"$cache" 2>/dev/null || true
	exit 0
fi

echo "find-devcontainer: could not uniquely identify the devcontainer for '$host_slug'." >&2
echo "Candidates:" >&2
for c in "${candidates[@]}"; do
	echo "  - $c" >&2
done
echo "Set DEVCONTAINER_CONTAINER=<name> to choose explicitly." >&2
exit 3
