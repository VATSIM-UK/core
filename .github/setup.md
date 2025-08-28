# Configuring a Development Environment

This repository uses [Node.js](https://github.com/VATSIM-UK/core/blob/main/package.json) and [PHP](https://github.com/VATSIM-UK/core/blob/main/composer.json)

## Video guide

For Windows users, there's a video guide available here: [YouTube video](https://www.youtube.com/watch?v=rAQn_PcjCqU)

*This guide sets up databases manually and does not use Docker which may be more complex for beginners.*

## Prerequisites

### Windows

- **Git CLI + Git Bash** -  Git is used for versioning and Git Bash is required to run any git hooks in this repo + it will make following the rest of the guide easier - https://git-scm.com/downloads/win
- **Docker Desktop** - If you don't want to go through running a database manually, Docker is recommended - https://docs.docker.com/desktop/setup/install/windows-install/
- **Node.js** - https://nodejs.org/en/download (see the prebuilt section)
- **Laravel Herd** - Herd is used to easily get PHP and Composer installed - https://herd.laravel.com/download/latest/windows

### Linux / Mac

*install via your package manager*

- **Git CLI**
- **Docker Engine** - https://docs.docker.com/engine/install/
- **Node.js**
- **PHP**

## Setup

### SSH keys

If you've never worked with Git, you will need to configure your [SSH keys](https://docs.github.com/en/authentication/connecting-to-github-with-ssh/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent#generating-a-new-ssh-key)

### Clone the repository

Create a fork of the repository, then clone your fork to your local machine using SSH

```shell
git clone git@github.com:<YOUR_USERNAME>/core.git
```

### Install the Composer dependencies and create an environment file by copying the example (`.env.example`)

```shell
composer install
composer install-hooks
cp .env.example .env
```

### Generate an application key

```shell
php artisan key:generate
```

### Modify required ENV variables in the `.env` file:

```
APP_URL=core.test # Do not include any trailing slashes

# Eliminate the need for Redis:
CACHE_DRIVER=array
QUEUE_DRIVER=sync
SESSION_DRIVER=file

DB_MYSQL_HOST=127.0.0.1

# Configure VATSIM OAuth or generate your own at https://auth-dev.vatsim.net/
VATSIM_OAUTH_BASE=https://auth-dev.vatsim.net
VATSIM_OAUTH_CLIENT=958
VATSIM_OAUTH_SECRET=l2JVotx1SsHY0ufTXDW1TVskUKm4UiZCpxFHiFwD
VATSIM_OAUTH_SCOPES=full_name,email,vatsim_details,country
```

### Start the database

In Git Bash / Bash run this command to start the databases

```shell
.devcontainer/load-dotenv.sh docker-compose -f .devcontainer/docker-compose.dev.yml up
```

*To stop the database later, press ctrl+c and re-run the command with `down` instead of `up`*

### Run migrations

```shell
php artisan migrate
php artisan cts:migrate:fresh # Optional if you require a CTS database for tests
```

### Seed the database

```shell
php artisan db:seed # sets up the roles and permissions
```

### Create a superuser

*List of accounts available in the OAuth sandbox is available here: [https://vatsim.dev/services/connect/sandbox](https://vatsim.dev/services/connect/sandbox)*

```shell
php artisan grant:superman <CID> # makes a test account an admin
```

### Compiling Frontend Assets

```shell
npm install # Install dependencies
npm run build # Build the assets
```

### Install Git hooks

Follow [this guide](https://github.com/VATSIM-UK/core/tree/main/.development/hooks) to install git hooks (optional but recommended)

### Start the web server

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
