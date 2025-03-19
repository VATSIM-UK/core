#!/bin/bash

# This script will load all environment variables from the .env file and then run the command passed as an argument.
# Example: .devcontainer/load-dotenv.sh docker-compose -f .devcontainer/docker-compose.dev.yml up

# Load environment variables from ../.env
export $(grep -v '^#' .env | xargs)

# Run the command passed as an argument
"$@"
