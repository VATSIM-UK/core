# GitHub Copilot Instructions for VATSIM UK Core

## Project Overview

VATSIM UK Core is the central membership and operations management system for VATSIM UK — a virtual air traffic control organisation on the VATSIM network. The application handles:

- **Member management** (VATSIM accounts, roles, states, qualifications, bans)
- **Training system** (waiting lists, training places, availability checks, retention checks)
- **Visit & Transfer applications** (members visiting/transferring to VATSIM UK)
- **ATC & Roster management**
- **Discord, TeamSpeak, and VATSIM network integrations**
- **Admin panel** (Filament v4)
- **Public-facing pages** and member dashboard

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Language | PHP 8.3+ |
| Framework | Laravel 12 |
| Admin UI | Filament v4 (`app/Filament/`) |
| Reactive UI | Livewire v3 (`app/Livewire/`) |
| Frontend JS | Alpine.js v3 |
| CSS | Tailwind CSS v4 + Bootstrap 5 (legacy pages) |
| Asset pipeline | Vite |
| Queue | Laravel Horizon (Redis) |
| Auth | Laravel Passport (OAuth2), VATSIM Connect SSO |
| DB | MySQL (two connections: `mysql` = core, `cts` = CTS) |
| Cache/Session | Redis |
| Testing | PHPUnit 12 via `php artisan test` |
| Linting | Laravel Pint (`composer lint`) |

## Repository Structure

```
app/
  Console/          Artisan commands
  Filament/         Filament admin resources (Admin/ and Training/ panels)
  Http/
    Controllers/    Traditional Laravel controllers
    Middleware/
    Requests/       Form request classes
  Jobs/             Queued jobs
  Livewire/         Livewire components
  Models/           Eloquent models
    Mship/          Core member models (Account is the central model)
    Training/       Training-related models (WaitingList, TrainingPlace, etc.)
    VisitTransfer/  Visit/Transfer application models
    Atc/, Sso/, etc.
  Policies/         Authorization policies
  Providers/        Service providers
  Services/         Service classes
  Libraries/        Standalone library wrappers (Discord, UKCP, etc.)
config/             Laravel config files
database/
  migrations/       Standard Laravel migrations
  factories/        Model factories
  seeders/          Database seeders
resources/
  views/            Blade templates (layout.blade.php is the main layout)
routes/
  web.php           Includes all web route files
  web-main.php      Authenticated member routes
  web-admin.php     Admin routes
  web-public.php    Public/unauthenticated routes
  web-external.php  External service routes
  web-livewire.php  Livewire-specific routes
  api.php           API routes
tests/
  Feature/          Feature tests (HTTP, integration)
  Unit/             Unit tests
```

## Key Models

- **`App\Models\Mship\Account`** — The central user/member model. Authenticated users are always instances of this class. Uses many `Concerns` traits (`HasRoles`, `HasStates`, `HasQualifications`, `HasBans`, `HasWaitingLists`, etc.).
- **`App\Models\Training\WaitingList`** — Training waiting list, with related `WaitingListAccount`, `WaitingListRetentionCheck`.
- **`App\Models\Training\TrainingPlace`** — Represents a training place offer/slot.
- **`App\Models\VisitTransfer\Application`** — Visit/transfer application.
- **`App\Models\Roster`** — Controller roster membership.
- All models extend `App\Models\Model` (which uses `TracksChanges` and `TracksEvents` traits) except some which extend Laravel/Filament base classes directly.

## Database

Two MySQL connections are always active:
- `mysql` (default) — Core application database (`DB_MYSQL_NAME`)
- `cts` — Controller Training System database (`CTS_DATABASE`)

The `tests/TestCase.php` base class uses `DatabaseTransactions` covering **both** connections: `$connectionsToTransact = [null, 'cts']`.

In CI, both databases (`core` and `core_testing`) must exist. The `.env.ci` file is used; it sets `APP_ENV=testing`, `CACHE_DRIVER=array`, `DB_MYSQL_HOST=localhost`, `DB_MYSQL_USER=root`, `DB_MYSQL_PASS=root`.

## Running the Application Locally

See `.github/setup.md` for the full guide. Key commands:

```bash
composer install
cp .env.example .env
php artisan key:generate
# Start DB (Docker):
.devcontainer/load-dotenv.sh docker-compose -f .devcontainer/docker-compose.dev.yml up
# Migrate and seed:
php artisan migrate
php artisan db:seed
npm install && npm run build
php artisan serve --host core.test --port 80
```

## Linting

```bash
composer lint          # Run Laravel Pint (auto-fix)
composer lint -- --test --parallel   # CI-style check only (no writes)
```

Pint is configured in `pint.json` with the `laravel` preset and `fully_qualified_strict_types` rule enabled. A pre-commit hook runs `composer lint` automatically if installed via `composer install-hooks`.

## Running Tests

```bash
# Full test suite (parallel, recreates DB):
php artisan test --parallel --processes=4 --recreate-databases

# Single file or filter:
php artisan test tests/Feature/Training/WaitingListTest.php
php artisan test --filter=SomeTestName
```

Tests use `DatabaseTransactions` (rolled back after each test, not wiped). The base `TestCase` automatically seeds the database and fakes notifications in `setUp()`.

Two commonly used test helpers are `$this->user` (a regular `member`-role account) and `$this->privacc` (an admin account with `*` permissions), both lazily created on first access.

## CI Workflow (`.github/workflows/test.yml`)

1. **Lint** job: PHP 8.4, Pint `--test --parallel`
2. **Test** job (needs lint): PHP 8.4, Node 24, MySQL (creates `core` and `core_testing` DBs), runs `php artisan test --parallel --processes=4 --recreate-databases`
3. **trigger-deploy** job: fires deploy workflow on push to `main`

## Code Style & Conventions

- **PHP style**: Laravel Pint with `laravel` preset. Fully qualified `use` statements are enforced (`fully_qualified_strict_types`). Always run `composer lint` before committing.
- **Branch naming**: `issue-[issue_number]` (e.g. `issue-1234`).
- **Commits**: Reference the issue (e.g. `fixes #22`). Keep PRs small and focused on one issue.
- **Namespaces**: Follow PSR-4 (`App\`, `Tests\`, `Database\Factories\`, `Database\Seeders\`).
- **Blade/views**: Blade templates live in `resources/views/`. The main layout is `resources/views/layout.blade.php`.
- **Frontend**: Tailwind CSS v4 for new components (Filament/Livewire), Bootstrap 5 for legacy pages. Alpine.js for interactivity without Livewire.
- **Indent**: 4 spaces (PHP, Blade, JS), 2 spaces (YAML, XML). LF line endings.

## Authorization

- Uses **Spatie Laravel Permission** for roles and permissions.
- Policies live in `app/Policies/` and follow Laravel's standard policy conventions.
- The `privacc` role with `*` permission is the super-admin role.
- Seeding (`php artisan db:seed`) sets up all default roles and permissions.

## Integrations

- **VATSIM Connect** — OAuth2 SSO (`VATSIM_OAUTH_*` env vars). Dev sandbox: `https://auth-dev.vatsim.net`
- **VATSIM API** — `VATSIM_API_BASE` / `VATSIM_API_KEY`
- **Discord** — Bot via `App\Libraries\Discord`, guild integration
- **TeamSpeak** — `App\Libraries\TeamSpeak` wrapper
- **Horizon** — Queue dashboard at `/horizon`
- **Telescope** — Debug assistant (dev only)
- **Filament** — Admin panel, config in `config/filament.php`

## Common Gotchas

1. **Two DB connections**: Any model or query touching CTS data must use `->on('cts')` or have `protected $connection = 'cts'` on the model. Tests must include `'cts'` in `$connectionsToTransact`.
2. **Parallel testing**: `AppServiceProvider::configureParallelTesting()` re-seeds and clears permission cache per parallel process. When adding new permissions/roles, always update seeders.
3. **Asset compilation required for tests**: CI compiles assets (`npm run build`) before running tests because some Blade views include Vite assets.
4. **`php artisan telescope:publish`** must be run before caching config in CI (it copies assets to `public/`).
5. **Watson Rememberable**: Some models use `Rememberable` for query caching. If a test sees stale data, call `->flushCache()` or use `Cache::flush()`.
6. **`Account` model lazy properties**: `$this->user` and `$this->privacc` in tests are populated via `__get` magic — they are created on first access, not in `setUp`.

## Creating New Features

When creating new features follow the existing patterns:
- **Models**: Extend `App\Models\Model`, add traits from `App\Models\Concerns/` as needed.
- **Filament resources**: Place in `app/Filament/Admin/Resources/` (admin panel) or `app/Filament/Training/` (training panel).
- **Livewire components**: Place in `app/Livewire/` with corresponding Blade view in `resources/views/livewire/`.
- **Form Requests**: Place in `app/Http/Requests/` mirroring the controller namespace.
- **Tests**: Feature tests in `tests/Feature/`, unit tests in `tests/Unit/`. Test filenames must end in `Test.php`.
- **Migrations**: Use `php artisan make:migration` with descriptive names. Date-prefixed automatically.
- **Factories**: Place in `database/factories/`, extend `Illuminate\Database\Eloquent\Factories\Factory`.
