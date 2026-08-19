# bin

The `bin` directory contains helper scripts used to build, configure and maintain the development container.

Each script has a single, well-defined responsibility and is intended to be reusable from Docker, the Dev Container lifecycle, or by developers when troubleshooting or re-running setup tasks.

Keeping these tasks as individual scripts, rather than embedding commands directly into `devcontainer.json`, improves readability, maintainability, and makes the development environment easier to extend.

---

# Purpose

The scripts within this directory automate the setup of the development environment by handling tasks such as:

- Initialising the development container.
- Installing project dependencies.
- Configuring Git and SSH.
- Preparing Laravel applications.
- Displaying helpful runtime information.
- Supporting local development workflows.

The scripts are called from various locations including:

- `devcontainer.json`
- Docker lifecycle commands
- PowerShell helper scripts
- Other setup scripts within this directory

Where appropriate, scripts are designed to be safely re-run during development.

---

# How It Works

The development environment is configured in stages.

Before the container is created, helper scripts prepare the required environment files.

During container creation, Dev Container lifecycle commands execute setup scripts to install dependencies and configure the environment.

Once the container has started, additional scripts complete any setup that depends on mounted volumes or user-specific configuration.

Some scripts can also be executed manually if a developer needs to repeat part of the setup process.

---

# Contributing

When adding new helper scripts:

## Give each script a single responsibility

Each script should perform one clearly defined task.

For example:

- Install a dependency
- Configure a tool
- Prepare application files
- Display information
- Configure developer credentials

Avoid creating large scripts that perform multiple unrelated operations.

## Follow the existing naming convention

Scripts should use descriptive names based on their purpose.

Examples:

```
setup-<feature>.sh
command-<lifecycle>.sh
```

This makes it immediately obvious when and why a script is executed.

## Make scripts safe to re-run

Where practical, scripts should be idempotent.

Running a script multiple times should not leave the development environment in an inconsistent state.

## Document assumptions

Each script should include comments explaining:

- why it exists
- when it runs
- any prerequisites
- whether it is safe to execute manually

This makes troubleshooting considerably easier for future contributors.

## Keep lifecycle commands simple

The Dev Container lifecycle commands should remain lightweight.

Where possible, complex logic should live inside scripts within this directory rather than directly inside `devcontainer.json`.

---

# Current Files

| File | Description |
|------|-------------|
| `bashrc.sh` | Creates the `/root/.bashrc` configuration and ensures `setup-info.sh` is executed whenever a terminal session is opened. |
| `command-post-create.sh` | Executed by the `postCreateCommand` defined in `devcontainer.json`. Performs the initial setup required after the container has been created. |
| `command-post-start.sh` | Executed by the `postStartCommand` defined in `devcontainer.json`. Performs tasks that should occur each time the container starts. |
| `copy-env.ps1` | PowerShell helper executed before the container starts. Copies the environment file into the location required by the Docker build so environment variables can be created correctly. |
| `setup-artisan.sh` | Prepares the Laravel application by generating the application key, running database migrations and seeding the database. |
| `setup-composer.sh` | Installs PHP dependencies by executing `composer install`. |
| `setup-git.sh` | Configures Git credentials required for development within the container. |
| `setup-info.sh` | Collects runtime information and displays a helpful summary when a new terminal session is opened. |
| `setup-prek.sh` | Installs Prek after the Git repository has been mounted, enabling project Git hooks. |
| `setup-ssh.sh` | Ensures SSH keys have the correct permissions and verifies authentication with GitHub. |

---

# Directory Structure

```
bin/
├── bashrc.sh
├── command-post-create.sh
├── command-post-start.sh
├── copy-env.ps1
├── setup-artisan.sh
├── setup-composer.sh
├── setup-git.sh
├── setup-info.sh
├── setup-prek.sh
└── setup-ssh.sh
```

---

# Summary

The `bin` directory provides the automation layer for the development environment.

Responsibilities are intentionally separated so that each script performs one task and can be maintained independently.

| Category | Responsibility |
|----------|----------------|
| Environment | Prepare configuration and environment files before container creation. |
| Dev Container Lifecycle | Execute setup during container creation and startup. |
| Application Setup | Install dependencies and initialise the Laravel application. |
| Developer Configuration | Configure Git, SSH and Git hooks. |
| User Experience | Display helpful runtime information when working inside the container. |

This approach keeps the Dev Container configuration clean, promotes reuse, and makes it easier for contributors to understand and extend the development environment.
