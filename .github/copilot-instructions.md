# GitHub Copilot Instructions for VATSIM UK Core

## Project Overview

VATSIM UK Core is the central membership and operations management system for VATSIM UK — a virtual air traffic control organisation on the VATSIM network. The application handles:

- **Member management** (VATSIM accounts, roles, states, qualifications, bans)
- **Training system** (waiting lists, training places, availability checks, retention checks, mentoring, exams)
- **Visit & Transfer applications** (members visiting/transferring to VATSIM UK)
- **ATC & Roster management**
- **Discord, TeamSpeak, and VATSIM network integrations**
- **Admin panel** (two Filament v4 panels: Admin and Training)
- **Public-facing pages** and member dashboard
- **External system integrations** (CTS, Moodle, Helpdesk, UKCP)

## Tech Stack

| Layer          | Technology                                                   |
| -------------- | ------------------------------------------------------------ |
| Language       | PHP 8.4+                                                     |
| Framework      | Laravel 12                                                   |
| Admin UI       | Filament v4 (`app/Filament/` — two panels: Admin + Training) |
| Reactive UI    | Livewire v3 (`app/Livewire/`)                                |
| Frontend JS    | Alpine.js v3                                                 |
| CSS            | Tailwind CSS v4 + Bootstrap 5 (legacy pages)                 |
| Asset pipeline | Vite                                                         |
| Queue          | Laravel Horizon (Redis)                                      |
| Auth           | Laravel Passport (OAuth2), VATSIM Connect SSO, Fortify (2FA) |
| DB             | MySQL (two connections: `mysql` = core, `cts` = CTS)         |
| Cache/Session  | Redis                                                        |
| Testing        | PHPUnit 12 via `php artisan test` (paratest for parallel)    |
| Linting        | Laravel Pint (`composer lint`)                               |

## Repository Structure

```
app/
  Auth/             Login flow helpers (LoginFlow, TwoFactorLoginRedirect)
  Console/          Artisan commands (27 commands: Members, Training, Discord, Roster, etc.)
  Contracts/        Interfaces (AccountCentricEvent)
  Enums/            Backed enums (BanTypeEnum, ExamResultEnum, 10 total)
  Events/           Domain events (35: Mship, Training, VisitTransfer, Discord, NetworkData)
  Exceptions/       Domain exceptions (22: VisitTransfer, Discord, TeamSpeak, Mship)
  Exports/          Laravel Excel exports (FeedbackExport)
  Filament/         Two Filament v4 panels (Admin + Training)
  Http/
    Controllers/    Traditional Laravel controllers (42)
    Middleware/     Custom middleware (17: access control, 2FA, bans, etc.)
    Requests/       Form request classes
  Infolists/        Custom Filament infolist components (PracticalExamCriteriaResult)
  Jobs/             Queued jobs (18: Training, Mship, Discord, ExternalServices)
  Libraries/        Standalone library wrappers (Discord, TeamSpeak, UKCP)
  Listeners/        Event listeners (31: sync, notifications, flag assignments)
  Livewire/         Livewire v3 components (22)
  Models/           Eloquent models
    Concerns/        Base model traits (TracksChanges, TracksEvents, OverridesUpdatedAt)
    Mship/           Core member models (Account is the central model)
      Concerns/      Account-specific traits (19: HasRoles, HasStates, HasBans, etc.)
    Training/       Training-related models (WaitingList, TrainingPlace, etc.)
    VisitTransfer/  Visit/Transfer application models
    Atc/, Cts/, Discord/, TeamSpeak/, Sso/, Sys/, etc.
  Notifications/    Notification classes (45+: Ban, Discord, Training, Roster, VisitTransfer)
  Observers/        Eloquent observers (DiscordTagObserver, EndorsementObserver, etc.)
  Policies/         Authorization policies (17)
  Providers/        Service providers (14, incl. Filament panels, Fortify, Horizon, Telescope)
  Registrars/       Custom Spatie PermissionRegistrar
  Repositories/     CTS data repositories (12)
  Scopes/           Eloquent global scopes (TeamSpeak/GroupScope)
  Services/         Business logic services (37: Training, Discord, TeamSpeak, etc.)
  Traits/           Reusable middleware traits (ExcludesRoutes, RedirectsOnFailure)
  View/Components/  Blade components (ProgressIndicator)
config/             Laravel config files (28)
database/
  migrations/       Standard Laravel migrations
  factories/        Model factories
  seeders/          Database seeders
docs/               Project documentation
  waitinglists/     Waiting list system docs (index.md)
resources/
  views/            Blade templates (layout.blade.php is the main layout)
routes/
  web.php           Master router — includes web-public, web-main, web-admin, web-external under domain group
  web-main.php      Authenticated member routes (auth_full_group) + semi-auth (vatsim-sso) + unauthenticated (retention token)
  web-admin.php     Removed legacy routes; all admin now via Filament at /admin (4-line placeholder)
  web-public.php    Public/unauthenticated static routes (home, policies, ATC/pilot pages)
  web-external.php  External webhook routes (VATSIM Net)
  web-livewire.php  Full-page Livewire routes (roster, retention checks) — loaded via ServiceProvider, not web.php
  fortify-two-factor.php  Fortify 2FA route group (prefix: auth/two-factor, guards: web)
  api.php           API routes (public + api_auth middleware)
  channels.php      Broadcast channels (empty, placeholder)
tests/
  Feature/          Feature tests (HTTP, integration)
  Unit/             Unit tests
```

## Key Models

- **`App\Models\Model`** — Base model for all domain models. Uses `TracksChanges` (audits attribute changes to `sys_data_changes`), `OverridesUpdatedAt` (nullable updated_at), and `TracksEvents` (logs lifecycle events to `sys_activity`). Models control this via `$tracked`/`$untracked` and `$trackedEvents` properties.
- **`App\Models\Mship\Account`** — The central user/member model (table: `mship_account`, PK: VATSIM CID). Implements `Authenticatable`, uses `SoftDeletes`, `HasApiTokens` (Passport), `TwoFactorAuthenticatable` (Fortify), `Rememberable`, `Notifiable`. Has 19 `Concerns` traits (`HasBans`, `HasCTSAccount`, `HasDiscordAccount`, `HasEmails`, `HasEmailSettings`, `HasEndorsement`, `HasHelpdeskAccount`, `HasMentoringPermissions`, `HasMoodleAccount`, `HasNetworkData`, `HasNotifications`, `HasPassword`, `HasQualifications`, `HasRoles`, `HasStates`, `HasTeamSpeakRegistrations`, `HasTwoFactor`, `HasVisitTransferApplications`, `HasWaitingLists`) each encapsulating a domain relationship set. Has `findOrRetrieve()` to auto-fetch from VATSIM Cert. The `HasRoles` concern overrides Spatie's `assignRole`/`removeRole` to fire app-specific events.
- **`App\Models\Mship\Account\Ban`**, **`Account\Email`**, **`Account\EmailSetting`**, **`Account\Endorsement`**, **`Account\EndorsementRequest`**, **`Account\Note`** — Sub-models under Account.
- **`App\Models\Mship\State`**, **`Mship\Qualification`**, **`Mship\Feedback\Form`**, **`Mship\Feedback\Question`** — Supporting member models.
- **`App\Models\Training\WaitingList`** — Training waiting list, with `WaitingListAccount`, `WaitingListFlag`, `WaitingListRetentionCheck`, `Removal`, `RemovalReason`.
- **`App\Models\Training\TrainingPlace`** — Training place/slot, with `TrainingPlaceOffer`, `TrainingPlaceLeaveOfAbsence`, `AvailabilityCheck`, `AvailabilityWarning`.
- **`App\Models\Training\TrainingPosition\TrainingPosition`** — Training position definitions.
- **`App\Models\Training\Mentoring\`** — Mentor training positions, scopes.
- **`App\Models\VisitTransfer\Application`** — Visit/transfer application, plus `Facility` and `Facility\Email`.
- **`App\Models\Roster`**, **`RosterHistory`**, **`RosterUpdate`** — Controller roster membership.
- **`App\Models\Atc\Position`**, **`PositionGroup`**, **`PositionGroupCondition`**, **`PositionGroupPosition`**, **`Endorseable`** — ATC position definitions and groups.
- **`App\Models\NetworkData\Atc`**, **`NetworkData\Pilot`** — Live VATSIM network data mirrors.
- **`App\Models\Airport`**, **`Airport\Runway`**, **`Airport\Procedure`** — Airport/airfield data.
- **`App\Models\Discord\DiscordTag`**, **`DiscordRoleRule`**, **`HoneypotStat`** — Discord integration.
- **`App\Models\TeamSpeak\Registration`**, **`TeamSpeak\Group`**, **`TeamSpeak\ServerGroup`**, etc. — TeamSpeak integration (8 models).
- **`App\Models\Cts\Booking`**, **`Cts\Member`**, **`Cts\Session`**, **`Cts\ExamResult`**, **`Cts\Validation`**, etc. — CTS training system mirror models (20+ models, read from CTS database).
- **`App\Models\Sys\Activity`**, **`Sys\Token`**, **`Sys\Notification`**, **`Sys\Data\Change`** — System/internal models.
- The base `App\Models\Model` uses `TracksChanges` and `TracksEvents` traits. Some integration models (Cts, some TeamSpeak) extend Eloquent directly or do not extend the base Model.

## Database

Two MySQL connections are always active:

- `mysql` (default) — Core application database (`DB_MYSQL_NAME`). Table naming uses prefixes like `mship_`, `vt_`, `sys_` from an earlier multi-schema era.
- `cts` — Controller Training System database (`CTS_DATABASE`). Read via `App\Models\Cts\*` models and `App\Repositories\Cts\*` repositories.

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

## Enums

Backed PHP enums live in `app/Enums/`:

- `BanTypeEnum`, `EmailType`, `ExamResultEnum`, `FieldScore`, `PilotExamType`
- `PositionValidationStatusEnum`, `QualificationTypeEnum`, `TrainingPlaceOfferStatus`
- `VTCheckStatus`, `AvailabilityCheckStatus`

## Services

Business logic service classes in `app/Services/` (37 services):

- **`Training\`** (22): WaitingListSelfEnrolment, WaitingListRetentionChecks, CheckWaitingListFlags, WriteWaitingListFlagSummary, TrainingPlaceService, TrainingPlaceOfferService, TrainingGroupStatisticsService, MentoringSessionsService, MentoringReportService, MentorPermissionService, MentoringAnnouncementService, TrainingSuccessesAnnouncementService, ExamAnnouncementService, ExamForwardingService, ExamHistoryService, ExamResubmissionService, CancelPendingExamService, OverrideExamReportService, EndorsementService, AvailabilityService, AvailabilityWarnings, ManualAtcUpgradeService
- **`Mship\`** (3): BanAccount, RepealBan, AddNote
- **`Discord\`** (1): HoneypotService
- **`TeamSpeak\`** (1): AtcServerGroupService
- **`Networkdata\`** (1): AtcNetworkdataService
- **`Roles\`** (1): DelegateRoleManagementService
- **`Markdown\`** (3): CustomMarkdownRenderer, ImageNodeRenderer, TableNodeRenderer
- **`Admin\`** (2): ATCTrainingStats, PilotTrainingStats

## Events, Listeners & Notifications

The app is event-driven. Key patterns:

- **Events** (`app/Events/`, 35) — Grouped under Mship, Training, VisitTransfer, Discord, NetworkData. E.g., `Mship\AccountAltered`, `Training\WaitingListFlagged`.
- **Listeners** (`app/Listeners/`, 31) — Handle Discord sync/removal, Mship sync (Moodle, Helpdesk, CTS), Training exam/mentoring/waiting-list notifications, VisitTransfer notifications.
- **Notifications** (`app/Notifications/`, 45+) — Laravel notifications for Ban, Discord, Endorsement, Feedback, Password, Welcome, Training (Exams, Mentoring, WaitingLists, TrainingPlace), Roster, VisitTransfer.

## Console Commands

Artisan commands in `app/Console/Commands/` (27 commands):

- **`Members\`**: ImportDivisionMembers, SyncExpiredBans, SyncCtsRoles
- **`Training\`**: CheckWaitingListEligibility, CreateWaitingListRetentionChecks, CheckAvailability, ImportCtsMembershipChecks, CreateCtsSessionRequests
- **`Discord\`**: SyncDiscordTags, RunDiscordBot
- **`TeamSpeak\`**: 3 commands (daemon, command, sync ATC groups)
- **`Roster\`**: UpdateRoster, UpdateRosterGanderControllers, CheckForNewS1ExamPasses
- **`NetworkData\`**: ProcessNetworkData
- **`Development\`**: GrantSuperman, CtsMock, CreateLocalAccount

## Filament Panels

Two Filament v4 panels, each with distinct access middleware and a dedicated `PanelProvider` in `app/Providers/Filament/`:

- **Admin Panel** (`path: /admin`, `id: 'app'`, `AdminPanelProvider`) — Resources: Accounts (9 relation managers: States, Roles, Bans, Notes, Qualifications, Endorsements, Feedback, RetentionChecks, VisitTransfer), Bans (with NotesRelationManager), Roles (with Users, Delegates), Positions, Feedback, RosterUpdates, RosterRestrictions, DiscordTags, VisitTransfer (Applications, Facilities). Pages: Dashboard, GenerateQuarterlyStats (ATC, Pilot, Operations). Widgets: AccountInfoWidget, RosterWidget, EndorsementWidget. 8 reusable base classes: `BasePage`, `BaseListRecordsPage`, `BaseEditRecordPage`, `BaseViewRecordPage`, `ChecksForGatedAttributes`, `DefinesGatedAttributes`, `LogPageAccess`, `LogRelationAccess`.

- **Training Panel** (`path: /training`, `id: 'training'`, `TrainingPanelProvider`) — Resources: WaitingLists (with AccountsRelationManager), EndorsementRequests, SoloEndorsements, TrainingPlaces (with category chart, offers overview), AccountResource (with WaitingListsRelationManager), PositionGroups (with MembershipEndorsementRelationManager). Pages: Dashboard, Exam (ConductExam, ManageExaminers, ExamSetup, Exams, ExamHistory, ViewExamReport), TheoryExam (Questions, History), Mentoring (dashboard, ConductMentoringSession, ManageMentors, History, UpcomingSessions, ViewMentoringReport), MyTraining (Availability, ExamHistory, MentoringHistory, PendingExams), EmailSettings, Statistics. Custom form components: `TrainingRichEditor`, `TrainingLinkAction`.

## CI Workflow (`.github/workflows/test.yml`)

1. **Lint** job: PHP 8.4, Pint `--test --parallel`
2. **Test** job (needs lint): PHP 8.4, Node 24, MySQL (creates `core` and `core_testing` DBs), runs `php artisan test --parallel --processes=4 --recreate-databases`
3. **trigger-deploy** job: fires deploy workflow on push to `main`

Other workflows: `deploy.yml` (production deployment), `copilot-setup-steps.yml` (Copilot environment setup).

Copilot skills live in `.github/skills/`: `configuring-horizon`, `laravel-best-practices`, `livewire-development`, `tailwindcss-development`. Each has a `SKILL.md` with guidelines — reference these for domain-specific conventions.

## Code Style & Conventions

- **PHP style**: Laravel Pint with `laravel` preset. Fully qualified `use` statements are enforced (`fully_qualified_strict_types`). Always run `composer lint` before committing.
- **Branch naming**: `issue-[issue_number]` (e.g. `issue-1234`).
- **Commits**: Reference the issue (e.g. `fixes #22`). Keep PRs small and focused on one issue.
- **Namespaces**: Follow PSR-4 (`App\`, `Tests\`, `Database\Factories\`, `Database\Seeders\`).
- **Blade/views**: Blade templates live in `resources/views/`. The main layout is `resources/views/layout.blade.php`.
- **Frontend**: Tailwind CSS v4 for new components (Filament/Livewire), Bootstrap 5 for legacy pages. Alpine.js for interactivity without Livewire.
- **Indent**: 4 spaces (PHP, Blade, JS), 2 spaces (YAML, XML). LF line endings.

## Authorization

- Uses **Spatie Laravel Permission** for roles and permissions. Custom table prefix: `mship_role`, `mship_permission`, `mship_account_role`, etc. Wildcard permissions are enabled (`privacc` role with `*` permission is super-admin).
- Policies live in `app/Policies/` (17 policies: Account, Password, Feedback, Role, PositionGroup, TrainingPlace, RosterUpdate, Ban, Note, EndorsementRequest, WaitingList, Mentoring, ManageMentors, Endorsement, Application, etc.).
- Seeding (`php artisan db:seed`) sets up all default roles and permissions. When adding new permissions/roles, update seeders.
- Role/permission cache is set to 24-hour expiry. Flush with `php artisan permission:cache-reset`.

## Integrations

- **VATSIM Connect** — OAuth2 SSO (`VATSIM_OAUTH_*` env vars). Dev sandbox: `https://auth-dev.vatsim.net`. Three auth guards: `vatsim-sso` (semi-auth), `web` (full), `api` (Passport).
- **VATSIM API** — `VATSIM_API_BASE` / `VATSIM_API_KEY`
- **CTS (Controller Training System)** — Separate MySQL database (`CTS_DATABASE`). Accessed via `App\Models\Cts\*` models (20+) and `App\Repositories\Cts\*` repositories (12). Core reads/writes CTS data for bookings, exams, mentoring, sessions.
- **Discord** — Bot via `App\Libraries\Discord` (discord-php v10), guild integration, role rules sync, honeypot anti-spam.
- **TeamSpeak** — `App\Libraries\TeamSpeak` wrapper around ts3-php-framework. Server group registration, ATC group sync.
- **Moodle** — User sync via `SyncToMoodle` job.
- **Helpdesk** — User sync via `SyncToHelpdesk` job.
- **UKCP** — Token generation for UK Controller Plugin (`App\Libraries\UKCP`).
- **Horizon** — Queue dashboard at `/horizon`
- **Telescope** — Debug assistant (dev only)
- **Filament** — Two panels (Admin + Training), config in `config/filament.php` and two panel providers in `app/Providers/Filament/`
- **Sentry / Bugsnag** — Error monitoring
- **Logtail** — Log shipping (`logtail/monolog-logtail`)

## Common Gotchas

1. **Two DB connections**: Any model or query touching CTS data must use `->on('cts')` or have `protected $connection = 'cts'` on the model. Tests must include `'cts'` in `$connectionsToTransact`.
2. **Parallel testing**: `AppServiceProvider::configureParallelTesting()` re-seeds and clears permission cache per parallel process. When adding new permissions/roles, always update seeders.
3. **Asset compilation required for tests**: CI compiles assets (`npm run build`) before running tests because some Blade views include Vite assets.
4. **`php artisan telescope:publish`** must be run before caching config in CI (it copies assets to `public/`).
5. **Watson Rememberable**: Some models use `Rememberable` for query caching. If a test sees stale data, call `->flushCache()` or use `Cache::flush()`.
6. **`Account` model lazy properties**: `$this->user` and `$this->privacc` in tests are populated via `__get` magic — they are created on first access, not in `setUp`.
7. **Custom base Model**: All domain models extend `App\Models\Model`, not Eloquent directly. The base model auto-audits changes (`TracksChanges`) and logs lifecycle events (`TracksEvents`). Some integration models (Cts\*, some TeamSpeak) extend Eloquent directly.
8. **Three auth guards**: `vatsim-sso` (semi-authenticated, pre-password), `web` (fully authenticated), `api` (Passport). Always check which guard you're working with.
9. **Model concerns pattern**: `Account` uses 19 `Concerns` traits (base model adds 3 more for change/event tracking). When adding new Account functionality, prefer creating a new Concern trait in `App\Models\Mship\Concerns\`.
10. **Event-driven sync**: Model changes fire events that trigger external syncs (Discord, TeamSpeak, Moodle, Helpdesk, CTS). When creating database writes in tests, use `createQuietly()` or `Notification::fake()` to avoid unwanted side effects.
11. **Notification faking**: `Notification::fake()` is called globally in the base `TestCase::setUp()`. Tests that assert on notifications must check `Notification::sent()` or use `Notification::assertSentTo()`. If a test needs real notifications, call `Notification::fake(false)` in its setup.

## Creating New Features

When creating new features follow the existing patterns:

- **Models**: Extend `App\Models\Model`, add traits from `App\Models\Concerns/` as needed. For integration models (Cts, Discord, TeamSpeak), extend Eloquent directly or follow the existing pattern in that namespace.
- **Filament resources**: Place in `app/Filament/Admin/Resources/` (admin panel) or `app/Filament/Training/Resources/` (training panel). Each panel has its own `PanelProvider` in `app/Providers/Filament/`.
- **Livewire components**: Place in `app/Livewire/` with corresponding Blade view in `resources/views/livewire/`.
- **Form Requests**: Place in `app/Http/Requests/` mirroring the controller namespace.
- **Services**: Business logic goes in `app/Services/`, grouped by domain subdirectory.
- **Jobs**: Queued work in `app/Jobs/`, grouped by domain.
- **Events/Listeners**: Events in `app/Events/`, listeners in `app/Listeners/`. Register in `EventServiceProvider` or domain-specific providers like `TrainingEventServiceProvider`.
- **Notifications**: Place in `app/Notifications/`, grouped by domain (e.g., `Notifications\Training\ExamNotification.php`).
- **Console commands**: Place in `app/Console/Commands/`, grouped by domain subdirectory.
- **Enums**: Use PHP 8.4 backed enums in `app/Enums/`.
- **Policies**: Place in `app/Policies/`, register in `AuthServiceProvider`.
- **Tests**: Feature tests in `tests/Feature/`, unit tests in `tests/Unit/`. Test filenames must end in `Test.php`.
- **Migrations**: Use `php artisan make:migration` with descriptive names. Date-prefixed automatically.
- **Factories**: Place in `database/factories/`, extend `Illuminate\Database\Eloquent\Factories\Factory`.
- **Routes**: Add to the appropriate route file — `web-public.php` (unauthenticated), `web-main.php` (authenticated members), `web-admin.php` (admin), `web-external.php` (webhooks), `web-livewire.php` (Livewire pages), `api.php` (API).
