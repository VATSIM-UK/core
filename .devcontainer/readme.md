

# .devcontainer

The `.devcontainer` directory contains the configuration required to build and run the project's development environment using **Visual Studio Code Dev Containers** (or any editor supporting the Dev Container specification).

Its purpose is to provide every developer with a consistent, reproducible development environment, eliminating "it works on my machine" issues by defining the container, its dependencies, and its lifecycle in source control.

---

# Purpose

The Dev Container is responsible for creating a fully configured development environment that is ready to use with minimal manual setup.

Rather than requiring developers to install PHP, Composer, Node.js, MySQL, or supporting tools locally, these are all provided within Docker containers.

The Dev Container configuration defines:

- How the development container is built.
- Which supporting services are started.
- Which files and folders are mounted.
- What commands run during container creation and startup.
- How the development environment is presented within VS Code.

The result is a repeatable environment that every contributor can use regardless of their host operating system.

---

# How It Works

Three primary files work together to create the development environment.

## `Dockerfile`

The `Dockerfile` defines **what the development container looks like**.

Its responsibilities include:

- Installing operating system packages.
- Installing PHP and supporting extensions.
- Installing Composer, Node.js and other development tools.
- Copying helper scripts into the image.
- Preparing the base environment that every developer will use.

Think of the Dockerfile as creating the blueprint for the development container.

---

## `docker-compose.dev.yml`

The Docker Compose file defines **which containers make up the development environment** and how they communicate.

Typical responsibilities include:

- Building the development container from the Dockerfile.
- Starting supporting services such as MySQL.
- Creating Docker networks.
- Mounting project folders.
- Mounting volumes.
- Providing environment variables.

While the Dockerfile defines an individual container, Docker Compose defines the complete development environment.

---

## `devcontainer.json`

The `devcontainer.json` file tells Visual Studio Code **how to use the Docker environment as a development workspace**.

Its responsibilities include:

- Selecting the Docker Compose configuration.
- Identifying which service becomes the development container.
- Installing VS Code extensions.
- Applying editor settings.
- Configuring forwarded ports.
- Executing lifecycle commands such as:
  - `postCreateCommand`
  - `postStartCommand`

These lifecycle commands invoke helper scripts within the project's `bin` directory, keeping the configuration clean and allowing setup logic to be maintained separately.

---

# How They Work Together

The following sequence occurs when a developer opens the project in a Dev Container.

```text
Developer opens project
          │
          ▼
VS Code reads devcontainer.json
          │
          ▼
docker-compose.dev.yml starts the required containers
          │
          ▼
Docker builds the development image using the Dockerfile
          │
          ▼
Container starts
          │
          ▼
postCreateCommand executes (first creation only)
          │
          ▼
postStartCommand executes (every container start)
          │
          ▼
Developer is presented with a fully configured workspace
```

Each file has a distinct responsibility:

| File | Responsibility |
|------|----------------|
| `Dockerfile` | Defines how the development container is built. |
| `docker-compose.dev.yml` | Defines the services, networks, volumes and relationships between containers. |
| `devcontainer.json` | Defines how VS Code uses the Docker environment and manages the development experience. |

Keeping these responsibilities separate makes the environment easier to understand and maintain.

---

# Contributing

When modifying the Dev Container configuration, ensure changes are made in the appropriate location.

## Update the Dockerfile when...

- Installing new software.
- Adding operating system packages.
- Installing language runtimes or tooling.
- Changing the base development image.

## Update Docker Compose when...

- Adding new services.
- Modifying networking.
- Changing mounted volumes.
- Updating environment variables.
- Configuring container dependencies.

## Update devcontainer.json when...

- Adding VS Code extensions.
- Changing editor settings.
- Modifying lifecycle commands.
- Forwarding additional ports.
- Configuring the developer experience.

## Keep responsibilities separate

Avoid placing complex shell commands directly inside `devcontainer.json`.

Instead, lifecycle commands should call helper scripts from the `bin` directory. This keeps configuration declarative while allowing setup logic to evolve independently.

---

# Current Files

| File | Description |
|------|-------------|
| `Dockerfile` | Defines the development container image, installs tooling and prepares the development environment. |
| `docker-compose.dev.yml` | Defines the development services, networking, volumes and container relationships. |
| `devcontainer.json` | Configures the Dev Container for VS Code, including lifecycle commands, extensions and workspace settings. |

---

# Directory Structure

```text
.devcontainer/
├── Dockerfile
├── devcontainer.json
└── docker-compose.dev.yml
```

---

# Summary

The Dev Container configuration is intentionally split into three complementary components.

| Component | Responsibility |
|----------|----------------|
| `Dockerfile` | Builds the development container image and installs all required software. |
| `docker-compose.dev.yml` | Orchestrates the complete development environment, including supporting services such as MySQL. |
| `devcontainer.json` | Integrates the Docker environment with VS Code and manages the developer experience through lifecycle commands and editor configuration. |

Together, these files provide a reproducible, version-controlled development environment that allows contributors to start developing with minimal manual configuration while keeping infrastructure, orchestration and editor configuration cleanly separated.

## Setup

Detailed instructions for creating and configuring the development environment are provided in two separate guides

1. [`pre-container-setup.md`](pre-container-setup.md) outlines the steps prior to creating the .devcontainer for the first time
2. [`post-container-setup.md`](post-container-setup.md) shows you what you need to do once the envirnoment is built

These guides walks through:

- Prerequisites for your host machine.
- Cloning the repository.
- Preparing the required environment files.
- Building and opening the Dev Container.
- First-time container initialisation.
- Verifying the development environment is working correctly.
- Troubleshooting common setup issues.

If you are setting up the project for the first time, or rebuilding your development environment from scratch, you should follow the steps in `pre-container-setup.md` and `post-container-setup.md` before making any changes to the project.
