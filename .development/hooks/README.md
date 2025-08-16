# Git Hooks

This directory contains Git hooks that are shared across the project to ensure code quality and consistency.

These hooks are stored in `.development/hooks/` to keep development-related files organized.

## Available Hooks

### pre-commit
- **Purpose**: Runs code style checks before each commit
- **Command**: `composer lint` (Laravel Pint)
- **Behavior**: Prevents commits if code style issues are found

## Installation

To install the Git hooks locally, run:

```bash
composer install-hooks
```

This will:
1. Copy the hooks from `.development/hooks/` to `.git/hooks/`
2. Make them executable
3. Confirm successful installation

## Manual Installation

If you prefer to install manually:

```bash
cp .development/hooks/pre-commit .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit
```

## For New Team Members

After cloning the repository, make sure to run:

```bash
composer install
composer install-hooks
```

This ensures you have all dependencies and the latest Git hooks configured.
