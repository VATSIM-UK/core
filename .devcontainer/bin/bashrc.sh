#!/bin/bash

# Run the shared setup info script to display info
SETUP_INFO="/workspace/.devcontainer/bin/setup-info.sh"

if [ -r "$SETUP_INFO" ]; then
    source "$SETUP_INFO"
fi
