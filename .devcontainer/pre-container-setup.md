# Development Environment Setup

This guide walks through setting up the VATSIM-UK development environment for the first time.

Where possible, this guide links to the official documentation for third-party tools rather than duplicating it.

---

# Prerequisites

Before continuing, ensure you have:

- A GitHub account.
- Git installed.
- Docker Desktop installed and running.
- Visual Studio Code.
- The **Dev Containers** extension for Visual Studio Code.

Refer to the official documentation for installation instructions where required.

---

# 1. Configure GitHub SSH Authentication

This project uses **SSH authentication** when communicating with GitHub.

If you have not already configured SSH keys for your GitHub account, follow the official GitHub guide:

- [GitHub: Generating a new SSH Key and adding it to the SSH Agent](https://docs.github.com/en/authentication/connecting-to-github-with-ssh/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent)
- [YouTube: Create & Add SSH Keys to GitHub for Authentication](https://www.youtube.com/watch?v=yVP3sYgd0bY)

Once complete, verify your authentication by running:

```bash
ssh -T git@github.com
```

You should receive a message confirming that authentication was successful.

---

# 2. Fork the Repository

Development should take place from **your own fork** of the VATSIM-Core repository rather than directly from the upstream project.

The VATSIM-Core repository is found at https://github.com/VATSIM-UK/core

Follow GitHub's official guide:

- [GitHub: How to fork a Repo](https://docs.github.com/en/pull-requests/how-tos/work-with-forks/fork-a-repo)

Once complete you will have a repository similar to:

```
https://github.com/<your-github-username>/core
```

---

# 3. Clone Your Fork

Clone **your fork**, not the upstream repository.

For example the below command will create the folder "core" within your current working directory.

```bash
git clone git@github.com:<your-github-username>/<repository>.git
```

Navigate into the project directory:

```bash
cd <repository>
```

If you want to clone the fork into the current working directory, the command is:

```bash
git clone git@github.com:<your-github-username>/<repository>.git .
```

---

# 4. Create a Development Branch

Before proceeding, please review the contributing guide here: [Contributing guide](../.github/CONTRIBUTING.md).

This will then outline if there any rules for the name of the branch. In this example, we will continue based on the
name of the branch being "my-new-feature"

Create a new branch for your work.

For example:

```bash
git checkout -b my-new-feature
```

Choose a branch name that clearly describes the work being undertaken.

Avoid committing directly to your fork's default branch.

---

# 5. Configure the Dev Container Environment

Before opening the project in a Dev Container, create the required environment file.

Copy:

```
.devcontainer/.env.example
```

to:

```
.devcontainer/.env
```

Populate the file with your Git identity.

Example:

```text
#.env file for devcontainer configuration
# This is different to the .env file used for the application itself.

# Environment variables for Git configuration
GIT_USER_NAME=John Smith
GIT_USER_EMAIL=address@example.com
```

These values are used to configure Git inside the development container.

This is done so that local machine settings for Git do not accidentially get included within the developmenr container.
This is particularly helpful for those contributors that have multiple GIT identities for different organisations.

---


# 6. Configure the Application Environment

Once the container has environment variables, we need to do the same for the Application.
Like the development container environment variables, these are automatically created within
the `docker-compose-dev.yml`

Copy:

```
.env.example
```

to:

```
.env
```

Open up the file and update these lines to contain the following information

```
VATSIM_OAUTH_CLIENT=958
VATSIM_OAUTH_SECRET=l2JVotx1SsHY0ufTXDW1TVskUKm4UiZCpxFHiFwD
```

# 7. Open the Project in the Dev Container

Open the project in Visual Studio Code.

When prompted, select:

> **Reopen in Container**

Alternatively, open the Command Palette (`Ctrl+Shift+P` / `Cmd+Shift+P`) and choose:

```
Dev Containers: Reopen in Container
```

Visual Studio Code will then:

1. Build the development container.
2. Start the supporting Docker services.
3. Execute the Dev Container lifecycle commands.
4. Configure the development environment automatically.

The first build may take several minutes depending on your internet connection.

---

# 8. Verify the Environment

Once the container has started successfully you should see a welcome message when opening a new terminal.

An example is:

```
Development container
────────────────────────────────────────────────────────────
🐳 Image: Debian GNU/Linux 12 (bookworm)
📂 PHP: PHP 8.4.23 (cli) (built: Jul 14 2026 01:31:15) (NTS)
💻 Git identity: John Smith (address@example.com)
✅ GitHub SSH authentication is working.
────────────────────────────────────────────────────────────

root@1f2a9b585c75:/workspace#
````

Verify that:

- Git is configured with your name and email.
- Composer dependencies have been installed.
- Laravel has been initialised.
- The application databases have been created.
- Database migrations have completed successfully.
- Seed data has been installed.
- SSH authentication to GitHub is working.

If any of these steps fail, review the terminal output for the relevant setup script.

---

# Troubleshooting

If you encounter issues during setup:

- Ensure Docker Desktop is running.
- Confirm your GitHub SSH authentication is working.
- Verify that `.devcontainer/.env` has been created correctly.
- Try rebuilding the Dev Container using:

```
Dev Containers: Rebuild Container
```

If database initialisation needs to be repeated, remove the existing Docker volumes before rebuilding the container.

Refer to the documentation in the `.devcontainer`, `bin`, and `mysql_init` directories for more information about how the development environment is constructed.

---

# Next Steps

Once the environment is running successfully, you're ready to begin development.

Remember to:

- Create feature branches for all work.
- Commit changes regularly.
- Push your branch to your GitHub fork.
- Open a Pull Request when your work is ready for review.
