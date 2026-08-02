# VATSIM UK Core

Central membership, training, and operations system for VATSIM UK: member/roles/states/bans, waiting lists, visits & transfers, ATC rosters, and Discord/TeamSpeak/CTS/Moodle/Helpdesk integrations.

**Stack:** Laravel 12 / PHP 8.4 · Filament v4 (two panels) · Livewire 3 + Alpine · Tailwind v4 (Bootstrap 5 for legacy pages) · Vite · MySQL (2 connections) · Redis/Horizon queues · Laravel Passport + VATSIM Connect SSO · PHPUnit 12.

## Commands

```bash
composer lint                       # Pint fix (also runs via prek pre-commit hook)
composer lint -- --test --parallel  # CI-style check, no writes
php artisan test                    # single process
php artisan test --parallel --processes=4 --recreate-databases  # CI style; needs core + core_testing DBs
php artisan test tests/Feature/Account/DashboardTest.php  # one file
php artisan test --filter=TestName  # focused
npm run build                       # REQUIRED before tests — some Blade views reference Vite assets
```

- Node 24 required (`.nvmrc`, `package.json` engines). Git hooks via `prek install` (Pint, blade-formatter, markdownlint, yaml/json checks).

## Local setup

```bash
cp .env.example .env && php artisan key:generate
.devcontainer/load-dotenv.sh docker-compose -f .devcontainer/docker-compose.dev.yml up  # DB in Docker
php artisan migrate && php artisan db:seed            # db:seed creates roles/permissions
php artisan serve --host core.test --port 80          # add `127.0.0.1 core.test` to /etc/hosts
```

Training panel dev data (personas 9000001/9000010/9000011/9000012 — **not** in the OAuth sandbox; log in with your own sandbox account, `grant:superman` it, then impersonate them from the admin panel):

```bash
php artisan cts:migrate:fresh
php artisan db:seed --class=Database\\Seeders\\LocalDevelopmentTrainingSeeder
php artisan grant:superman <CID>   # promote a sandbox account to admin
```

CI moves `.env.ci` → `.env` and runs `php artisan telescope:publish` before `config:cache`/`route:cache`.

## Database

Two MySQL connections are **always active** — never assume data is in the default connection:

- `mysql` (default) — core app DB (`DB_MYSQL_NAME`). Tables carry `mship_`, `vt_`, `sys_` prefixes.
- `cts` — Controller Training System DB (`CTS_DATABASE`, or `CTS_DATABASE_URL`). Access via `App\Models\Cts\*` models and `App\Repositories\Cts\*`; CTS models use `->on('cts')` or `protected $connection = 'cts'`.
- In `testing` with `TEST_TOKEN` set, the CTS DB name is suffixed `_test_<token>` (see `config/database.php`) so parallel processes don't collide.

## Architecture

- **Base model `App\Models\Model`** (extends Eloquent) adds `TracksChanges` (audits attribute changes) and `TracksEvents` (logs lifecycle events to `sys_activity`) via `$tracked`/`$untracked` and `$trackedEvents` props. Domain models extend this; integration models (`Cts\*`, some TeamSpeak) extend Eloquent directly.
- **`App\Models\Mship\Account`** is the central model (PK = VATSIM CID): `Authenticatable`, `SoftDeletes`, Passport tokens, Fortify 2FA, `Rememberable`. Its 19 `Concerns\*` traits (`HasRoles`, `HasBans`, `HasStates`, `HasCTSAccount`, ...) each wrap a domain relationship set. Key methods: `findOrRetrieve()` auto-fetches from VATSIM Cert if not in DB; `HasRoles` overrides Spatie's `assignRole`/`removeRole` to fire app-specific events. Prefer a new Concern trait when adding Account functionality.
- **Event-driven syncs.** Model changes fire events → listeners (`app/Listeners/`) → external syncs (Discord, TeamSpeak, Moodle, Helpdesk, CTS). Events are registered in `EventServiceProvider` and domain-specific providers like `TrainingEventServiceProvider`. In tests, write via `createQuietly()` or direct DB inserts to avoid side effects.
- **Auth guards** (three, don't mix them up): `vatsim-sso` (semi-auth, pre-password setup), `web` (fully authenticated), `api` (Passport OAuth2). VATSIM Connect SSO via `VATSIM_OAUTH_*`; dev sandbox `https://auth-dev.vatsim.net`.
- **Authorization:** Spatie Laravel Permission with prefixed tables; wildcard `*` permission on the `privacc` role = super-admin. New roles/permissions must be added to `RolesAndPermissionsSeeder` (parallel tests re-seed and flush the permission cache per process). Permission cache has 24-hour TTL; manually flush with `php artisan permission:cache-reset`.
- **Routes** are split across `routes/*.php`. `web-livewire.php` is loaded by `RouteServiceProvider::mapWebRoutes()`, **not** `web.php`. Others: `web-public.php` (unauthenticated), `web-main.php` (members), `web-admin.php` (legacy admin placeholder — admin is Filament at `/admin`), `web-external.php` (webhooks), `fortify-two-factor.php`, `api.php`.
- **Filament panels** (`app/Filament/`, providers in `app/Providers/Filament/`): Admin (`/admin`, id `app`) and Training (`/training`). Reusable Admin base classes live in `app/Filament/Admin/Helpers/Pages/` (`BasePage`, `BaseListRecordsPage`, `BaseEditRecordPage`, `BaseViewRecordPage`, `ChecksForGatedAttributes`, `LogPageAccess`, `LogRelationAccess`) and `app/Filament/Admin/Helpers/Resources/` (`DefinesGatedAttributes`); training-panel helpers under `app/Filament/Training/Concerns/`.
- **Scheduling:** commands registered in `app/Console/Kernel.php::schedule()`, monitored by `spatie/laravel-schedule-monitor` (except `doNotMonitor()` ones like `horizon:snapshot`). Horizon handles queues (`QUEUE_CONNECTION=queue`).

## Code organization

When adding new code follow these locations — the app is organized by domain subdirectory, not by layer:

| What | Where | Example |
|------|-------|---------|
| Domain models | `app/Models/<Domain>/` | `App\Models\Training\WaitingList` |
| Model Concerns (traits) | `app/Models/<Domain>/Concerns/` | `App\Models\Mship\Concerns\HasBans` |
| Base model traits | `app/Models/Concerns/` | `TracksChanges`, `TracksEvents` |
| Business logic | `app/Services/<Domain>/` | `App\Services\Training\EndorsementService` |
| Queued jobs | `app/Jobs/<Domain>/` | `App\Jobs\Training\SyncToMoodle` |
| Events | `app/Events/<Domain>/` | `App\Events\Mship\AccountAltered` |
| Listeners | `app/Listeners/<Domain>/` | `App\Listeners\Training\Endorsement\` |
| Notifications | `app/Notifications/<Domain>/` | `App\Notifications\Training\ExamNotification` |
| Console commands | `app/Console/Commands/<Domain>/` | `App\Console\Commands\Roster\UpdateRoster` |
| Policies | `app/Policies/` (flat or `<Domain>/` subdir) | `App\Policies\AccountPolicy` |
| Enums | `app/Enums/` (flat, PHP 8.4 backed enums) | `BanTypeEnum`, `ExamResultEnum` |
| CTS data access | `app/Repositories/Cts/` | `BookingRepository`, `StudentRepository` |
| External API wrappers | `app/Libraries/` | `App\Libraries\Discord`, `App\Libraries\TeamSpeak` |
| Livewire components | `app/Livewire/` + view in `resources/views/livewire/` | |
| Filament Admin resources | `app/Filament/Admin/Resources/` | |
| Filament Training resources | `app/Filament/Training/Resources/` | |
| Form requests | `app/Http/Requests/` mirroring controller namespace | |

- **Integration models** (Cts, Discord, TeamSpeak) extend Eloquent directly, not `App\Models\Model`. Their tables live on the `cts` connection.
- Events/listeners are registered in `EventServiceProvider` or domain-specific providers (`TrainingEventServiceProvider`). Check before adding new wiring.

## Testing gotchas

- `tests/TestCase.php` uses `DatabaseTransactions` on **both** connections (`[null, 'cts']`), calls `$this->seed()`, and fakes notifications globally in `setUp()`.
- `$this->user` (member role) and `$this->privacc` (`*` super-admin) are created **lazily** on first access via `__get`, not in `setUp`. Both are created with `createQuietly()`.
- Some models use `Rememberable` query caching — call `->flushCache()` or `Cache::flush()` if a test sees stale data.
- `VATSIM_API_BASE` points at a fake endpoint in tests; don't rely on real network calls.
- `Notification::fake()` is global. If you need real notifications in a specific test, call `Notification::fake(false)` in that test's setUp.

## Conventions

- **Commits:** reference the issue (e.g. `fixes #1234`); keep PRs small and focused on one issue.
- **PR descriptions:** mark dependencies between PRs with `Depends on #N` or `Blocked by #N` — a status check will flag the PR until resolved.
- **External contributions:** new features or bug fixes should have an issue opened first; code ownership transfers to VATSIM UK via CLA.
- **Logging:** `Log` facade, static message + structured context array (never interpolation), e.g. `Log::warning('CTS position not found', ['training_place_id' => $place->id])`. Bugsnag alerts on `notice`+ — reserve those levels for things a human must investigate; use `info`/`debug` for routine/expected conditions. Log caught exceptions with `['exception' => $e]`.
- **Auditing:** actor-attributed state changes (bans, role grants, endorsements) go through the global `audit()` helper (`app/helpers.php`) → `audit` log channel. Not for errors.
- **Blade:** formatted by blade-formatter with **tabs** (`.bladeformatterrc.json`), even though `.editorconfig` says 4 spaces; `.bladeignore` lists files the formatter skips.
- **Frontend:** Tailwind v4 for new Filament/Livewire components; Bootstrap 5 for legacy pages; Alpine for interactivity without Livewire. Asset source files live under `resources/assets/` (sass, css, js) — not the default `resources/css/` or `resources/js/`.
- **PHP style:** Pint `laravel` preset + `fully_qualified_strict_types` (fully-qualified imports enforced). Run `composer lint` before committing.
