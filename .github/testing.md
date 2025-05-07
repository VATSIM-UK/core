### Development Environment
This repository uses [NodeJS](https://github.com/VATSIM-UK/core/blob/main/package.json) and [PHP](https://github.com/VATSIM-UK/core/blob/main/composer.json)

If you use VS Code and Docker, our repository contains a dev container configuration that will 
automatically launch a ready-to-go development environment (including databases).
Click "Open in Dev Container" when prompted, or run the task from the F1 menu.

A docker-compose file is provided in `.devcontainer/docker-compose.yml`.

On Windows you may wish to use Laravel Herd instead, 
you will need to run MySQL and create `core` and `cts` databases separately.

### Setup
Generally, this project follows the [standard installation instructions](https://laravel.com/docs/installation)
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

Run migrations.

```shell
php artisan migrate
php artisan cts:migrate:fresh # Optional if you require a CTS db for tests
```

For local dev you may need to run
```shell
php artisan db:seed # sets up the roles and permissions
php artisan grant:superman <CID> # makes a test account an admin
```

### Compiling Frontend Assets
Install all required dependencies
```shell
npm install
```

Compile the assets.
```shell
npm run build
npm run dev # if you need hot reload etc
```

### Configure

Set your `APP_URL` to where you are running Core, e.g `core.test`. 
**Important:** do not include any trailing slashes in the url

In order to log in to Core you need Vatsim Connect set up. See docs [here](https://vatsim.dev/services/connect/sandbox)
for information on what usernames and passwords this supports.

When running core as `core.test` you may use the following env settings (in .env)

```
VATSIM_OAUTH_BASE=https://auth-dev.vatsim.net
VATSIM_OAUTH_CLIENT=958
VATSIM_OAUTH_SECRET=l2JVotx1SsHY0ufTXDW1TVskUKm4UiZCpxFHiFwD
VATSIM_OAUTH_SCOPES=full_name,email,vatsim_details,country
```


### Run Tests

```shell
php artisan test
```

If you wish you can use a separate `.env.testing` for testing.
