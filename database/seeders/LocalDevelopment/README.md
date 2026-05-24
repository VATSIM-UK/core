# Local development seeders

Opt-in seeders for scaffolding data used when working on the training panel locally. They are **not** run by `php artisan db:seed` (which only seeds roles and permissions).

## Quick start

```shell
php artisan migrate
php artisan cts:migrate:fresh
php artisan db:seed
php artisan db:seed --class=Database\\Seeders\\LocalDevelopmentTrainingSeeder
php artisan grant:superman <your-sandbox-cid>
```

Log in with your [VATSIM sandbox](https://vatsim.dev/services/connect/sandbox) account, open the admin panel, and **impersonate** the fictional personas below. Those CIDs are not in the OAuth sandbox.

Training panel URL: `/training`

## Orchestrator

| Seeder | Command class |
|--------|----------------|
| Entry point | `Database\Seeders\LocalDevelopmentTrainingSeeder` |

Runs all training seeders in order, resets shared state between runs, only runs in `local` or `testing` environments, and prints a summary table of personas and training positions.

```shell
php artisan db:seed --class=Database\\Seeders\\LocalDevelopmentTrainingSeeder
```

## Training panel seeders

Located under `database/seeders/LocalDevelopment/Training/`.

### `AtcAndCtsTrainingPositionsSeeder`

Prepares position data required before waiting lists, training places, or exams can be wired up.

1. Calls `Database\Seeders\Testing\PositionsAndEndorsementsSeeder` (EGLL / EGKK / military ATC positions and position groups).
2. Ensures matching **CTS** `positions` rows exist for each dev callsign.
3. Creates **training position** records (`training_positions`) with `cts_positions`, `cts_primary_position`, and `exam_callsign`.

| Callsign | Type | Notes |
|----------|------|--------|
| `EGKK_TWR` | Tower | Gatwick TWR training track |
| `EGLL_N_APP` | Approach | Heathrow north APP training track |

Created models are stored on `DevTrainingFoundation::$trainingPositionsByCallsign` for use by other seeders in the same run.

Run in isolation:

```shell
php artisan db:seed --class=Database\\Seeders\\LocalDevelopment\\Training\\AtcAndCtsTrainingPositionsSeeder
```

### `DevTrainingRolesSeeder`

Creates Spatie roles and attaches permissions (requires `RolesAndPermissionsSeeder` to have run first):

| Role | Permissions |
|------|-------------|
| `dev-training-staff` | Waiting lists, training places, exams, mentors (see `DevTrainingPersonas::STAFF_PERMISSIONS`) |
| `dev-training-student` | `training.access` |

```shell
php artisan db:seed --class=Database\\Seeders\\LocalDevelopment\\Training\\DevTrainingRolesSeeder
```

#### Dev roles vs production

`dev-training-staff` and `dev-training-student` exist **only for local seeding**. They are convenience bundles of permissions and do **not** mirror real production roles (for example `ATC Examiner (TWR)`, division staff roles, or per-list delegated permissions).

The training panel authorises actions via **permission** checks (`training.access`, `waiting-lists.view.atc`, and so on) in policies and Filament — not by role name. When adding or changing access control:

- Write and run tests that assert the relevant **permission** (or policy behaviour), not membership of a dev role.
- Treat dev personas as a quick way to click through the panel locally; do not assume that passing as `dev-training-staff` proves production role configuration is correct.

Production role ↔ permission mapping is defined in operational processes and [`RolesAndPermissionsSeeder`](../../RolesAndPermissionsSeeder.php); dev roles are maintained separately in [`DevTrainingPersonas`](Training/DevTrainingPersonas.php).

### `DevTrainingPersonasSeeder`

Creates fictional **mship** accounts with linked **CTS** `members` and assigns the roles above.

| Persona | CID | Email | Role |
|---------|-----|-------|------|
| Staff | `9000001` | `dev-training-staff@example.test` | `dev-training-staff` |
| Student | `9000010` | `dev-training-student@example.test` | `dev-training-student` |
| Student (LOA) | `9000011` | `dev-training-student-loa@example.test` | `dev-training-student` |
| Student (exams) | `9000012` | `dev-training-student-exams@example.test` | `dev-training-student` |

All student accounts receive the **S1** qualification (from the schema dump). Accounts are exposed on `DevTrainingFoundation` for use by other seeders in the same run.

Constants live in `DevTrainingPersonas` — change CIDs there if they clash with other local data.

Run in isolation:

```shell
php artisan db:seed --class=Database\\Seeders\\LocalDevelopment\\Training\\DevTrainingPersonasSeeder
```

### `TrainingPlaceAvailabilitySeeder`

Creates ad-hoc **training places** and related compliance data:

| Scenario | Account | Callsign | Data |
|----------|---------|----------|------|
| Availability checks | `9000010` | `EGKK_TWR` | Passed + failed checks, pending warning, CTS `availability`, mentoring session history |
| Leave of absence | `9000011` | `EGLL_N_APP` | Active LOA on training place, mentoring session history |

Requires `DevTrainingPersonasSeeder` and `AtcAndCtsTrainingPositionsSeeder` to have run first.

```shell
php artisan db:seed --class=Database\\Seeders\\LocalDevelopment\\Training\\TrainingPlaceAvailabilitySeeder
```

### `CtsExamsAndMentoringSeeder`

Seeds **CTS** practical exam and mentoring records for account `9000012`:

| Scenario | Panel surface |
|----------|----------------|
| Exam request (`taken=0`) + `exam_setup` | Exam requests table |
| Scheduled exam (`taken=1`) + `practical_examiners` | Accepted exams / student pending exams |
| Completed exam (`finished=1`) + `practical_results` | Exam history |
| Cancelled exam + `cancel_reason` | Training place exam cancellations |
| Mentoring session history (completed, cancelled, no-show, upcoming) + open request | Training place mentoring history / mentor mentoring page |
| Staff mentor on `EGKK_TWR` | Manage mentors |

Staff (`9000001`) receives `ExaminerSettings` (TWR) and a `MentorTrainingPosition` via `MentorPermissionService`.

```shell
php artisan db:seed --class=Database\\Seeders\\LocalDevelopment\\Training\\CtsExamsAndMentoringSeeder
```

## Supporting code

| File | Purpose |
|------|---------|
| `DevTrainingPersonas` | Fixed CIDs and emails for fictional accounts |
| `DevTrainingFoundation` | In-memory registry (`$trainingPositionsByCallsign`, `$trainingPlacesByKey`, persona accounts) |
| `Concerns\AssignsDevTrainingRoles` | `syncRoles` for dev staff / student roles |
| `Concerns\CreatesLinkedAccount` | `firstOrCreate` mship `Account` + CTS `Member` + qualifications |
| `Concerns\CreatesDevTrainingPlace` | Ad-hoc training place via `TrainingPlaceService` (CTS validations) |
| `Concerns\SeedsCtsPosition` | `firstOrCreate` core `Position` and CTS `Position` for a callsign |
| `Concerns\SeedsDevMentoringSessions` | Historical and pending CTS `sessions` for dev students |

## Idempotency

Seeders use `firstOrCreate` / `updateOrCreate` with stable keys (CIDs, callsigns, `position_id`). Re-running the orchestrator is safe and will not duplicate training positions or personas.

## Related seeders (manual, not part of orchestrator)

| Seeder | Purpose |
|--------|---------|
| `Database\Seeders\Testing\PositionsAndEndorsementsSeeder` | Used internally by `AtcAndCtsTrainingPositionsSeeder` |
| `Database\Seeders\Testing\CtsExamSeeder` | Legacy sample CTS exam bookings (unlinked to core accounts) |
| `Database\Seeders\WaitingListStressSeeder` | Performance testing (~201 accounts on one list) |

## Tests

```shell
php artisan test --filter=LocalDevelopmentTrainingSeederTest
```

## CTS notes

- Local CTS uses the mock schema from `php artisan cts:migrate:fresh` (see `.github/setup.md`).
- Personas are **not** synced via `Account::syncToCTS()` (requires an OAuth client named `CT System`). CTS `Member` rows are inserted directly, matching the pattern used in feature tests.
