#!/bin/bash

# -----------------------------------------------------------------------------
# Setup Information Display
# -----------------------------------------------------------------------------
# This script displays information about the development container, including
# the PHP version, Git identity, and GitHub SSH status.
# -----------------------------------------------------------------------------

# Stop quietly for non-interactive shells.
case "$-" in
    *i*) ;;
    *)
        if [[ "${BASH_SOURCE[0]}" != "$0" ]]; then
            return 0
        else
            exit 0
        fi
        ;;
esac

echo
echo "Development container"
echo "────────────────────────────────────────────────────────────"

if [ -r /etc/os-release ]; then
    . /etc/os-release
    echo "🐳 Image: ${PRETTY_NAME:-Unknown}"
fi

if command -v php >/dev/null 2>&1; then
    echo "📂 PHP: $(php -v | head -n 1)"
else
    echo "⛔ PHP: Not installed"
fi

git_name="$(git config --global user.name 2>/dev/null || true)"
git_email="$(git config --global user.email 2>/dev/null || true)"

if [ -n "${git_name}" ] || [ -n "${git_email}" ]; then
    echo "💻 Git identity: ${git_name:-Not configured} (${git_email:-Not configured})"
else
    echo "⛔ Git identity: Not configured"
fi

if [ -r /root/.github-ssh-status ]; then
    github_ssh_status="$(cat /root/.github-ssh-status)"
    echo "${github_ssh_status}"
fi

echo "────────────────────────────────────────────────────────────"
echo
