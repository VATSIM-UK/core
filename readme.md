# Core

Core application for VATSIM UK
[www.vatsim.uk](https://vatsim.uk)

## Installation
### Cloning This Repository
Clone this repository to your local machine and enter the directory that is created
```shell
git clone git@github.com:VATSIM-UK/nexus.git vatsim-uk-nexus
cd vatsim-uk-nexus
```

### Development Environment
This repository targets PHP 8.1 and Node JS 18.x.

If you use VS Code and Docker, our repository contains a dev container configuration that will 
automatically launch a ready-to-go development environment (including databases).
Click "Open in Dev Container" when prompted, or run the task from the F1 menu.

A docker-compose file is provided in `.devcontainer/docker-compose.yml`.

### Setup
Generally, this project follows the [standard installation instructions](https://laravel.com/docs/9.x/installation)
relating to Laravel.
The following is an abbreviated guide to get started quickly.

Install the Composer dependencies and create an environment file by copying the example (`.env.example`).
```shell
composer install
cp .env.example .env
```

Generate an application key.
```shell
php artisan key:generate
```

Build the backend.

```shell
composer build
```

### Compiling Frontend Assets
Install all required dependencies
```shell
yarn
```

Compile the assets.
```shell
yarn dev
```

Depending on your node version you may need.
```shell
NODE_OPTIONS=--openssl-legacy-provider yarn dev
```
