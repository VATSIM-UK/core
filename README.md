[master_build_status]: https://travis-ci.com/VATSIM-UK/core.svg?branch=master
[master_style_ci_status]: https://github.styleci.io/repos/75443611/shield?branch=master
[code_climate_maintainability]: https://api.codeclimate.com/v1/badges/6a47acbf3b7798883e7e/maintainability
[master_codecov_status]: https://codecov.io/gh/VATSIM-UK/core/branch/master/graphs/badge.svg
[staging_status]: https://vatsim-uk.deploybot.com/badge/88313865825892/135269.png
[production_status]: https://vatsim-uk.deploybot.com/badge/88313865825892/93858.png

<p align="center">
    <a href="https://www.vatsim.uk"><img src="https://vatsim.uk/system/view/images/logo.png" width="250px" /></a>
</p>

# About

Core is the flagship application of VATSIM UK. Originally designed to handle Single Sign-On (SSO) for our other applications, it now serves as the main hub for all member information and any new features we introduce.

# Status

|      Check      |                            Provider                           |              Status             |
|-----------------|---------------------------------------------------------------|---------------------------------|
| Build           | [TravisCI](https://travis-ci.com/VATSIM-UK/core)              | ![master_build_status]          |
| Code Style      | [StyleCI](https://github.styleci.io/repos/75443611)           | ![master_style_ci_status]       |
| Maintainability | [CodeClimate](https://codeclimate.com/github/VATSIM-UK/core)  | ![code_climate_maintainability] |
| Coverage        | [CodeCov](https://codecov.io/gh/VATSIM-UK/core/branch/master) | ![master_codecov_status]        |

#Environments
|     Env    |              URL              |        Status        |
|------------|-------------------------------|----------------------|
| Production | https://core.vatsim.uk        | ![production_status] |
| Staging    | https://beta.core.vatsim.uk   | ![staging_status]    |

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
