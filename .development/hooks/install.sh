#!/usr/bin/env bash

# Install git hooks script
# This script symlinks files in .development/hooks to the .git/hooks directory.

# Check if the script is run with --force flag
force=false
for arg in "$@"; do
    [[ $arg == "--force" ]] && force=true
done

# Test if symlinks are supported (clean up after test)
tmp_src="$(mktemp -t symlink_test_src.XXXXXX 2>/dev/null || echo ".symlink_test_src_$$")"
echo "" > "$tmp_src"
tmp_link="${tmp_src}_link"

if ! ln -s "$tmp_src" "$tmp_link" 2>/dev/null || [ ! -L "$tmp_link" ]; then
    echo "⚠️ Symlinks are not supported in this environment. File contents will be copied instead of symlinks."
fi

rm -f "$tmp_src" "$tmp_link"


echo "⚙️ Installing git hooks..."

# Go through each file in the hooks directory
# and create a symlink for each file without an extension
# in the .git/hooks directory.
for file in .development/hooks/*; do
    if [ -f "$file" ]; then
        base=$(basename "$file")
        # skip files with an extension (keep only extensionless hook scripts)
        [[ $base == *.* ]] &&  continue;

        target=".git/hooks/$base"

        # If the --force flag is set, remove the existing target
        if $force && [ -e "$target" ]; then
            echo "Removing existing target before recreating: $target"
            rm -f "$target"
        fi

        # Create a symlink if it doesn't already exist
        if [ ! -e "$target" ]; then
            echo "Creating symlink for $base"
            ln -s "../../$file" "$target" 2>/dev/null || ln -s "$file" "$target"
            chmod +x "$file"
        else
            echo "⚔️ Hook $base is already registered, skipping. Force override with --force."
        fi
    fi
done
