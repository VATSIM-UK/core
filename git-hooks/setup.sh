#!/usr/bin/env bash

for file in $(ls ./git-hooks | grep -v '\.sh$'); do
    chmod +x ./git-hooks/$file
    ln -sf ../../git-hooks/$file .git/hooks/$file
    echo "Initialized $file hook"
done
