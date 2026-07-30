# Post-Container Setup

Once the Dev Container has been successfully built and the automated setup has completed, there are a small number of manual steps required before you can fully use the development environment.

---

# 1. Sign in to the VATSIM Connect Sandbox

VATSIM provides a dedicated **Connect Sandbox** environment for development and testing of OAuth authentication.

This allows you to authenticate with the application without using a live VATSIM account.

## Sandbox Accounts

A list of available sandbox accounts can be found at:

- https://vatsim.dev/services/connect/sandbox

Each account lists its:

- CID
- Username
- Password

> **Note**
>
> The username and password for each sandbox account are the same.

## Sign In

Open:

- https://vatsim.dev/services/connect/sandbox

Sign in using one of the published sandbox accounts.

Make a note of the **CID** for the account you use, as it will be required in the next step.

---

# 2. Grant Yourself Administrator Access

After signing into the sandbox, create a local administrator account within your development environment.

From a terminal inside the Dev Container, execute:

```bash
php artisan grant:superman <CID>
```

Replace `<CID>` with the CID of the sandbox account you used in the previous step.

For example:

```bash
php artisan grant:superman 1234567
```

If successful, Artisan will confirm that **Superman** access has been granted.

---

# 3. Verify the Account

You can verify that the administrator account has been created by inspecting the development database.

The easiest way is through **phpMyAdmin**, which is available as part of the development environment.

Open:

- http://127.0.0.1:8090/

Log in using:

| Setting | Value |
|---------|-------|
| Server | `mysql` |
| Username | `root` |
| Password | `root` |

Navigate to the **`code`** database and open the **`mship_account`** table.

Your sandbox account should now be present with the appropriate administrator permissions.

---

# Setup Complete

Your development environment is now fully configured.

You should now be able to:

- Sign in using the VATSIM Connect Sandbox.
- Access administrator-only functionality.
- Develop and test features locally.
- Run Laravel Artisan commands as required.

Happy coding!
