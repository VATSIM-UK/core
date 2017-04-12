[![StyleCI](https://styleci.io/repos/75443611/shield?branch=development&style=flat)](https://styleci.io/repos/75443611)
[![Stories in Ready](https://badge.waffle.io/VATSIM-UK/core.png?label=ready&title=Ready)](http://waffle.io/VATSIM-UK/core)
[![Code Climate](https://codeclimate.com/github/VATSIM-UK/core/badges/gpa.svg)](https://codeclimate.com/github/VATSIM-UK/core)
[![Build Status](https://travis-ci.org/VATSIM-UK/core.svg?branch=production)](https://travis-ci.org/VATSIM-UK/core)

## Upgrade Notes

The following are the upgrade notes for deploying in production.

### All Versions
1. Stop the queue and TeamSpeak daemon
2. Disable cronjobs
3. Run `composer install -o --no-dev`
4. Run `php artisan migrate --step --force --no-interaction`
5. Run `php artisan module:migrate --step --force --no-interaction`
6. Run `npm install --production`
7. Run `gulp --production`
8. **Perform version-specific upgrade steps (below)**
9. Enable all cronjobs
10. Restart all queue processes

### 2.4.5.x to 2.4.6
1. Run `php artisan view:clear`
2. Run `php artisan route:clear`

### Mail Refactoring
1. Add `notification` queue to queue processor

### Older Versions

To upgrade from older versions, check the `README.md` file for that release.
