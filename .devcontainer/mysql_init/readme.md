# mysql_init

The `mysql_init` directory contains SQL scripts that are automatically executed when the MySQL Docker container is initialised for the **first time**.

Its sole responsibility within this development environment is to create the databases required by the various applications. Database schema creation, migrations, and seeding are intentionally **not** performed here.

---

# Purpose

When the development container is created with a **new, empty MySQL data volume**, the official MySQL Docker image automatically executes any SQL files found in:

```
/docker-entrypoint-initdb.d
```

The project's Docker Compose configuration mounts the local `mysql_init` directory into this location.

This repository uses the directory for one purpose only:

- Create the required application databases.

Once these databases exist, each application is responsible for managing its own schema.

For example, within **VATSIM-UK Core**, the database schema and seed data are created using Laravel's migration and seeding system via Artisan commands (for example, `php artisan migrate` and `php artisan db:seed`).

Keeping responsibilities separate means:

- Docker creates the databases.
- Each application owns and manages its own schema.
- Schema changes remain version-controlled alongside the application code rather than in shared Docker initialisation scripts.

---

# How It Works

During the **first startup only**, MySQL performs the following process:

1. Detects that the data directory is empty.
2. Initialises the MySQL server.
3. Executes every `.sql` file inside `/docker-entrypoint-initdb.d`.
4. Executes files in **alphabetical order**.

Currently, there is only a single initialisation script:

```
01-databases.sql
```

This script creates the required databases so that the applications can subsequently connect and run their own migrations.

---

# Important Behaviour

These scripts **do not run every time the container starts**.

They are only executed when MySQL is creating a brand-new database volume.

If the database volume already exists, MySQL assumes the server has already been initialised and skips every file in this directory.

If you need to recreate the databases from scratch during development, remove the existing database volume first:

```bash
docker compose down -v
```

or:

```bash
docker volume rm <volume-name>
```

> **Warning**
>
> Removing the volume permanently deletes all stored database data.

---

# Contributing

The scope of this directory should remain intentionally small.

Only add SQL here if it is required during **initial MySQL server creation**, such as:

- Creating additional application databases.
- Creating development database users.
- Assigning development permissions.

Do **not** add:

- Table definitions.
- Indexes.
- Stored procedures.
- Seed data.
- Application schema.

Those belong within the owning application's migration system.

For Laravel applications such as **VATSIM-UK Core**, all schema changes should be implemented as migrations and executed using Artisan.

If additional initialisation scripts become necessary, prefix filenames with numbers to preserve execution order, for example:

```
01-databases.sql
02-users.sql
03-permissions.sql
```

---

# Current Files

| File | Description |
|------|-------------|
| `01-databases.sql` | Creates the required application databases (`core` and `cts`) and grants development permissions to the MySQL `root` user. No schema or application data is created here. |

---

# Directory Structure

```
mysql_init/
└── 01-databases.sql
```

---

# Summary

Responsibilities are intentionally separated:

| Component | Responsibility |
|----------|----------------|
| `mysql_init` | Create the required MySQL databases. |
| VATSIM-UK Core | Create and update the database schema using Laravel migrations (`php artisan migrate`). |
| VATSIM-UK Core | Populate initial or development data using Laravel seeders (`php artisan db:seed`). |

This separation keeps infrastructure concerns within the Docker environment while allowing each application to fully own and evolve its database schema through its normal development workflow.
