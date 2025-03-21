#!/usr/bin/env bash

# This script will initialize all the hooks in the git-hooks directory
# Running: chmod +x git-hooks/setup.sh && ./git-hooks/setup.sh

for file in $(ls ./git-hooks | grep -v '\.sh$'); do
    chmod +x ./git-hooks/$file
    ln -sf ../../git-hooks/$file .git/hooks/$file
    echo "Initialized $file hook"
done
