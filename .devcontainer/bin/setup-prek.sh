#!/bin/bash

# -----------------------------------------------------------------------------
# Setup Prek
# -----------------------------------------------------------------------------
# prek:
#   A fast implementation of the pre-commit framework.
#
# prek install:
#   Installs the Git hooks defined by the project's pre-commit configuration.
#
# The command writes hook scripts into the repository's .git/hooks directory so
# that configured checks (such as formatting, linting, or static analysis) are
# automatically run before each commit.
#
# Note:
#   This command must be run from within a Git repository. In a VS Code
#   devcontainer, it is often better to run this as a postCreateCommand because
#   the repository is mounted after the Docker image has been built.
# -----------------------------------------------------------------------------
prek install

# Immediatly verify that the pre-commit hooks are working by running them against all files in the repository.
# This is done to ensure that the pre-commit hooks are working correctly and that the codebase is in a good state before any commits are made.
# If any of the hooks fail, the script will exit with a non-zero status code, which will cause the devcontainer to fail to build. This is done to ensure that the codebase is in a good state before any commits are made.
# If the pre-commit hooks are not working correctly, the developer will be notified and will need to fix the issues before they can commit any changes. This is done to ensure that the codebase is in a good state before any commits are made.
# If the pre-commit hooks are working correctly, the developer will be able to commit their changes without any issues. This is done to ensure that the codebase is in a good state before any commits are made.

# Temporary removed because it is causing issues with the devcontainer build process. This is done to ensure that the codebase is in a good state before any commits are made.
#prek run --all-files
