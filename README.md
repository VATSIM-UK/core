<p align="center">
  <a href="https://travis-ci.org/VATSIM-UK/core"><img src="https://travis-ci.org/VATSIM-UK/core.svg" alt="Build Status"></a>
  <a href="https://styleci.io/repos/75443611"><img src="https://styleci.io/repos/75443611/shield?style=flat" alt="Style Status"></a>
  <a href="https://codeclimate.com/github/VATSIM-UK/core/maintainability"><img src="https://api.codeclimate.com/v1/badges/17d97541d889dde173d8/maintainability" alt="Maintainability"></a>
  <a href="https://codeclimate.com/github/VATSIM-UK/core/test_coverage"><img src="https://api.codeclimate.com/v1/badges/17d97541d889dde173d8/test_coverage" alt="Test Coverage"></a>
</p>

# About

Core is the flagship application of VATSIM UK. Originally designed to handle Single Sign-On (SSO) for our other applications, it now serves as the main hub for all member information and any new features we introduce.

# Issue Tracking

To find out how to track and manage issues, please visit [https://github.com/VATSIM-UK/core/wiki/Reporting-and-Tracking-Issues](https://github.com/VATSIM-UK/core/wiki/Reporting-and-Tracking-Issues).

# Upgrade Notes

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

### 3.6.0

* Run `php artisan storage:link`
* Add `MAPS_API_KEY` in `.env`

### Older Versions

To upgrade from older versions, check the `README.md` file for that release.
