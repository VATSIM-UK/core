[![StyleCI](https://styleci.io/repos/75443611/shield?branch=development&style=flat)](https://styleci.io/repos/75443611)
[![Code Climate](https://codeclimate.com/github/VATSIM-UK/core/badges/gpa.svg)](https://codeclimate.com/github/VATSIM-UK/core)
[![Build Status](https://travis-ci.org/VATSIM-UK/core.svg?branch=production)](https://travis-ci.org/VATSIM-UK/core)

## Upgrade Notes

The following are the upgrade notes for deploying in production.

### All Versions

1. Stop the queue and TeamSpeak daemon
2. Disable cronjobs
3. Run `composer install --optimize-autoloader --no-dev` (dev: `composer install`)
4. Run `php artisan migrate --step --force --no-interaction`
6. Run `npm install --production` (dev: `npm install`)
7. Run `gulp --production` (dev: `gulp`)
8. **Perform version-specific upgrade steps (below)**
9. Enable all cronjobs
10. Restart the queue and TeamSpeak daemon

### 2.4.5.x to 3.0.0

Upgrading to version 3 will break services that authenticate with Core using
SSO. All services must be updated to use the new OAuth API for authentication
and authorization.

Upgrading to version 3 will break module functionality. Any deployment commands
and processes relating to modules (e.g. enabling modules, running migrations)
should be disabled.

1. Remove 'caffeinated/module' commands from deployment scripts.
1. Run `php artisan view:clear`
2. Run `php artisan route:clear`
1. Run `php artisan passport:keys`
3. Add `notification` queue to queue processor
2. Add OAuth clients using `php artisan passport:client`
3. Make sure all new client IDs correspond to `mship_oauth_emails`
(preferably by updating the emails table, not forcing the ids of the clients)
1. Update any external services to use the new OAuth API for authenticating users.

### Older Versions

To upgrade from older versions, check the `README.md` file for that release.
