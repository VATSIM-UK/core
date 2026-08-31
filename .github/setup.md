# Configuring a Development Environment

This repository uses [Node.js](../package.json) and [PHP](../composer.json)

There are two ways to get set up:

- **[Dev container](#quick-start-dev-container)** *(recommended)* - PHP, Node, MySQL, Redis and all
  the tooling are built for you inside Docker. Nothing but Docker and VS Code on your machine.
- **[Manual setup](#manual-setup)** - install PHP, Node and the databases yourself. Use this if you
  prefer Laravel Herd, or you don't want to work inside a container.

## Quick start: Dev container

### What you need

- **Docker Desktop** - <https://docs.docker.com/desktop/> (Windows, Mac) or <https://docs.docker.com/engine/install/> (Linux)
- **Visual Studio Code** - <https://code.visualstudio.com/>
- **Dev Containers extension** - <https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers>
- **Git** - <https://git-scm.com/downloads> (and [SSH keys](https://docs.github.com/en/authentication/connecting-to-github-with-ssh/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent#generating-a-new-ssh-key) configured for GitHub)

### 1. Fork and clone

Create a fork of the repository, then clone your fork:

```shell
git clone git@github.com:<YOUR_USERNAME>/core.git
```

**Because the way devcontainers work, if you clone this repository onto a drive with an NTFS filesystem (default on Windows), you will experience insane slow downs. It is therefore recommended to install clone the repo into a WSL distro, open it there in vscode and then open it in the devcontainer.**

The guide for [setting up a WSL distro](https://docs.microsoft.com/en-us/windows/wsl/install) is fairly simple. It is recommended to use Debian or its derivatives (Ubuntu, etc.).

### 2. Open the repository in the container

Install VS Code in your WSL distro and open the folder in there and accept the **Reopen in Container** prompt (or run
`Dev Containers: Build and Reopen in Container` from the command palette).

### 3. Point `core.test` at your machine

`core.test` is not a real domain, so add it to the hosts file **on your host machine** (not inside
the container):

- Windows: `c:\Windows\System32\Drivers\etc\hosts` (requires Admin privileges to modify)
- Mac/Linux: `/etc/hosts`

Append this to the file:

```text
127.0.0.1 core.test
```

### 4. Open it

If you're working on the frontend, run Vite in a terminal inside the container for live rebuilds:

```shell
make frontend-dev
```

Log in at <http://core.test> with any account from the
[VATSIM OAuth sandbox](https://vatsim.dev/services/connect/sandbox) - the sandbox credentials are
already configured, and each account's password is its own CID (e.g. CID `10000005`, password
`10000005`).

All eleven sandbox accounts (`10000000`-`10000010`) are created during setup and already hold the
superman (`privacc`) role, so you land in the admin panel at `/admin` without any extra steps. Need
a non-admin to test against? Create one with `php artisan local:create-account`.

### What's in the container

| Service | Where |
|---------|-------|
| App | <http://core.test> (port 80, served by Apache from `public/`) |
| Vite | port 5173 |
| phpMyAdmin | <http://localhost:8090> (server `mysql`, user `root`, password `root`) |
| MySQL | `mysql:3306` inside the container, `localhost:13306` from your host |
| Redis | `redis:6379`, password `root` (unused by default - `.env` uses the `array`/`sync`/`file` drivers) |
| Databases | `core`, `cts`, `core_testing` |

Tests, linting and git all work as they do locally:

```shell
# single-threaded test run
php artisan test
# parallel test run (faster)
php artisan test --parallel --recreate-databases
composer lint
prek run --all-files
```

## Manual setup

### Video guide

For Windows users, there's a video guide available here: [YouTube video](https://www.youtube.com/watch?v=rAQn_PcjCqU)

*This guide sets up databases manually and does not use Docker which may be more complex for beginners.*

### Prerequisites

#### Windows

- **Git CLI + Git Bash** -  Git is used for versioning and Git Bash is required to run any git hooks in this repo + it will make following the rest of the guide easier - <https://git-scm.com/downloads/win>
- **Docker Desktop** - If you don't want to go through running a database manually, Docker is recommended - <https://docs.docker.com/desktop/setup/install/windows-install/>
- **Node.js** - <https://nodejs.org/en/download> (see the prebuilt section)
- **Laravel Herd** - Herd is used to easily get PHP and Composer installed - <https://herd.laravel.com/download/latest/windows>

#### Linux / Mac

*install via your package manager*

- **Git CLI**
- **Docker Engine** - <https://docs.docker.com/engine/install/>
- **Node.js**
- **PHP**

### Setup

#### SSH keys

If you've never worked with Git, you will need to configure your [SSH keys](https://docs.github.com/en/authentication/connecting-to-github-with-ssh/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent#generating-a-new-ssh-key)

#### Clone the repository

Create a fork of the repository, then clone your fork to your local machine using SSH

```shell
git clone git@github.com:<YOUR_USERNAME>/core.git
```

#### Install the Composer dependencies and create an environment file by copying the example (`.env.example`)

```shell
composer install
composer install-hooks
cp .env.example .env
```

#### Generate an application key

```shell
php artisan key:generate
```

#### Modify required ENV variables in the `.env` file

`.env.example` defaults to the dev container's service hostnames, so point them back at your own
machine:

```text
DB_MYSQL_HOST=127.0.0.1
REDIS_HOST=127.0.0.1
```

Everything else is already set up for local development - `APP_URL=http://core.test`, the shared
VATSIM Connect sandbox client, and `CACHE_DRIVER=array` / `QUEUE_DRIVER=sync` /
`SESSION_DRIVER=file` so you don't need Redis at all.

You may also want to generate your own OAuth client at <https://auth-dev.vatsim.net/> rather than
using the shared sandbox one, and adjust `DB_MYSQL_PORT` if you're running MySQL yourself instead of
via Docker.

#### Start the database

In Git Bash / Bash run this command to start the databases

```shell
.devcontainer/load-dotenv.sh docker-compose -f .devcontainer/docker-compose.dev.yml up
```

*To stop the database later, press ctrl+c and re-run the command with `down` instead of `up`*

#### Run migrations

```shell
php artisan migrate
php artisan cts:migrate:fresh # Optional if you require a CTS database for tests
```

#### Seed the database

```shell
php artisan db:seed # sets up the roles and permissions
```

#### Seed training panel development data (optional)

If you work on the training panel locally, seed ATC/CTS positions, training positions, and fictional staff/student personas:

```shell
php artisan cts:migrate:fresh
php artisan db:seed --class=Database\\Seeders\\LocalDevelopmentTrainingSeeder
```

| Persona | CID | Use |
|---------|-----|-----|
| Staff | `9000001` | Admin, examiner, mentor (impersonate from admin) |
| Student | `9000010` | Availability checks / warnings |
| Student (LOA) | `9000011` | Leave of absence |
| Student (exams) | `9000012` | Exams and mentoring history |

Log in with your [sandbox](https://vatsim.dev/services/connect/sandbox) account (`grant:superman`) first - the persona CIDs are not in OAuth.

Full reference: [`database/seeders/LocalDevelopment/README.md`](../database/seeders/LocalDevelopment/README.md).

#### Create a superuser

*List of accounts available in the OAuth sandbox is available here: [https://vatsim.dev/services/connect/sandbox](https://vatsim.dev/services/connect/sandbox)*

```shell
php artisan grant:superman <CID> # makes a test account an admin
```

#### Compiling Frontend Assets

```shell
npm install # Install dependencies
npm run build # Build the assets
```

#### Install Git hooks

Follow <https://github.com/j178/prek> installation instructions

Install hooks:

```bash
prek install
```

Now the pre commit hooks will run automatically on every commit.

#### Using the Makefile (Simplified Alternative for Unix-based Systems)

For Linux and Mac users, a `Makefile` is available to simplify running common development tasks after the initial setup is complete.

**Note:** All the setup steps above must be completed before using the makefile.

The two main commands you'll need are:

```shell
# Start the web server
make serve

# Or customise the port
make serve PORT=8000

# Start Vite for real-time asset compilation (in a separate terminal)
make frontend-dev
```

Other available commands for maintenance:

- `make dev` - Complete setup in one command (Docker, dependencies, build, and server)
- `make docker` - Start Docker services in the background
- `make docker-down` - Stop Docker services
- `make npm` - Install npm dependencies
- `make build` - Install Composer dependencies and compile frontend assets

#### Start the web server

Since `core.test` is not a standard local domain, you may need to modify your hosts file.

- On Windows, this will be `c:\Windows\System32\Drivers\etc\hosts` (requires Admin privileges to modify)
- On Mac/Linux, this will be `/etc/hosts`

and append the following line:

```text
127.0.0.1 core.test
```

To start the web server, run

```shell
php artisan serve --host core.test --port 80
```

### Run Tests

```shell
php artisan test
```

If you wish you can use a separate `.env.testing` for testing.
