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
6. Run `npm install`
7. Run `npm run prod` (dev: `npm run dev`)
8. **Perform version-specific upgrade steps (below)**
9. Enable all cronjobs
10. Restart the queue and TeamSpeak daemon

### 3.4.0

* No additional steps required.

### Older Versions

To upgrade from older versions, check the `README.md` file for that release.
