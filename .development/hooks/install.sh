#!/usr/bin/env bash


# Goes through all files in the .development/hooks directory
# without a file extension and creates a symlink in the .git/hooks directory

for file in .development/hooks/*; do
    echo "Processing $file..."
    if [ -f "$file" ] && [[ ! "$file" =~ \.[^.]+$ ]]; then
        hook_name=$(basename "$file")
        target=".git/hooks/$hook_name"

        if [ ! -e "$target" ]; then
            echo "Creating symlink for $hook_name"
            ln -s "$file" "$target"
        else
            echo "Hook $hook_name already exists, skipping."
        fi
    fi
    echo "skipped $file"
done
