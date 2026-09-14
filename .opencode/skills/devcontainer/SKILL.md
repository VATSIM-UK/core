---
name: devcontainer
description: Use ONLY on Windows hosts (win32, Git Bash/MSYS/CYGWIN, WSL) when working in this repo - the Windows checkout is a stale or placeholder copy and php, composer, node, or gh are only in the Docker devcontainer. Routes every command and repo file read/write through docker exec. Do NOT use on Linux or macOS, where the devcontainer runs directly from the repo and normal tools are correct.
---

# Working in the Devcontainer (Windows hosts only)

## Platform Gate - check this FIRST

```bash
case "$(uname -s)" in
MINGW* | MSYS* | CYGWIN*) echo windows ;;
*) echo other ;;
esac
```

- **Windows** (`MINGW*`, `MSYS*`, `CYGWIN*`, or `$OS` contains `Windows_NT`):
  this skill applies. Follow it for all repo work.
- **Linux / macOS** (`other`): **STOP - do not use this skill.** The
  devcontainer runs directly from the repo, so the working tree the harness
  sees *is* the real repo. Use the normal `read` / `write` / `edit` tools and
  run commands normally. Only reach for `docker exec` if the user explicitly
  asks for it.

## Overview

On Windows the harness checkout of this repo is frequently a **stale or
placeholder copy** (the real repo lives in a WSL-backed Docker devcontainer),
and the Windows host lacks the toolchain. Routing through the container is the
only way to see the real files and run the real toolchain.

## The Iron Rule

On Windows, do **not** use the host `read`, `write`, `edit`, `glob`, `grep`, or
`list` tools on repo files. Do **not** run `php`, `composer`, `npm`, `node`, or
`git` against the repo in the host `bash`. Use `docker exec` for all of it.

The repo root inside the container is `/workspace`.

## Quick Reference

| Task           | Command                                                                                  |
| -------------- | ---------------------------------------------------------------------------------------- |
| Find container | `bash .opencode/skills/devcontainer/scripts/find-devcontainer.sh`                        |
| Run a command  | `bash .opencode/skills/devcontainer/scripts/dc.sh '<cmd>'`                               |
| Read a file    | `bash .opencode/skills/devcontainer/scripts/dc.sh 'cat -- /workspace/<path>'`            |
| Write a file   | `bash .opencode/skills/devcontainer/scripts/dc.sh 'cat > /workspace/<path>'` then heredoc |
| Binary / exact | `... dc.sh 'base64 -w0 /workspace/<path>'` (read), `base64 -d` (write)                   |

`dc.sh` runs as the devcontainer user (`vscode`) in `/workspace` with a login
shell, so `php`, `composer`, `node`, `git` and `gh` are on `PATH`, and stdin is
forwarded (heredocs work). Overrides: `DEVCONTAINER_CONTAINER`,
`DEVCONTAINER_USER`, `DEVCONTAINER_WORKSPACE`.

## Container Discovery

`find-devcontainer.sh` probes running containers, matches this repo by its git
remote (falling back to the folder name from the `devcontainer.local_folder`
label), caches the answer, and prints the container name.

If it exits non-zero, **stop and report the candidates** - never fall back to
running on the host. Pin a container with `DEVCONTAINER_CONTAINER=<name>`.

## Read, Write, Edit

- **Read:** `dc.sh 'cat -- /workspace/path/to/file'`
- **Write exact content:** heredoc into `cat >`:

  ```bash
  bash .opencode/skills/devcontainer/scripts/dc.sh 'cat > /workspace/notes.txt' <<'EOF'
  line one
  line two
  EOF
  ```

- **Edit:** read the file, compute the full new content, rewrite with `cat >`.
  The host `edit` tool cannot reach the container.
- **Binary:** read with `base64 -w0`, write with `base64 -d`.

## Git Bash Path Gotcha

Invoking `docker exec` directly from Git Bash fails with
`Cwd must be an absolute path` because MSYS rewrites `/workspace` into a
Windows path. The helper scripts set `MSYS_NO_PATHCONV=1` for you; if you call
`docker exec` directly, set it yourself.

## Red Flags - STOP

- You are on **Linux/macOS** and about to route through `docker exec` anyway.
- About to call `read` / `edit` / `write` / `grep` / `glob` on a repo file on Windows.
- About to run `php`, `composer`, `npm`, `node`, `php artisan test`, or
  `git status` directly in the Windows host `bash`.
- "The container probably isn't running, so I'll use the host" - re-check with
  `docker ps`; if there is genuinely no container, stop and say so.
- "It's a small edit, the host copy is probably fine" - the Windows copy is stale.
- "I'll write it on the host and copy it in later" - write to `/workspace` now.

## Common Mistakes

| Mistake                          | Fix                                            |
| -------------------------------- | ---------------------------------------------- |
| Enforcing this on Linux/macOS    | don't - the repo is mounted directly           |
| Host `read` tool on Windows      | `dc.sh 'cat -- /workspace/...'`                |
| `php -v` on the Windows host     | `dc.sh 'php -v'`                               |
| Editing a Windows host file      | rewrite the file in the container              |
| `docker exec -w /workspace` fails | use `dc.sh` (sets `MSYS_NO_PATHCONV=1`)       |
| Assumed no container running     | run `find-devcontainer.sh`, don't guess        |
