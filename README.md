[![StyleCI](https://styleci.io/repos/75443611/shield?branch=development&style=flat)](https://styleci.io/repos/75443611)
[![Stories in Ready](https://badge.waffle.io/VATSIM-UK/core.png?label=ready&title=Ready)](http://waffle.io/VATSIM-UK/core)
[![Code Climate](https://codeclimate.com/github/VATSIM-UK/core/badges/gpa.svg)](https://codeclimate.com/github/VATSIM-UK/core)
[![Build Status](https://travis-ci.org/VATSIM-UK/core.svg?branch=production)](https://travis-ci.org/VATSIM-UK/core)

## Upgrade Notes

### All Versions (2.3.5 onwards)
* Stop all queue processes
* Disable all cronjobs
* Run `composer install -o --no-dev`
* Run `php artisan migrate --step -vvv`
* Run `npm install --production`
* Run `gulp --production`
* **Perform version-specific upgrade steps (below)**
* Check for any additional BugSnag filter (in version-specific steps below)
* Enable all cronjobs
* Restart all queue processes

### 2.4.2 > 2.4.4
* No specific upgrades to run.

### 2.4.1 > 2.4.2
* Run the Community Migrat `php artisan module:migrate community`
* Run `php artisan module:migrate networkdata`

### 2.4 > 2.4.1
* Nothing to do.

### 2.3.6.1 > 2.4
* Run the Community Migration `php artisan module:migrate community`
* Run the VisitTransfer Migration `php artisan module:migrate visittransfer`

### 2.3.6 > 2.3.6.1
* Add relevant BUGSNAP_ config variables from the .env.example file
* Ensure following filters are set, to avoid reporting them in BugSnag reports:
 * username - to filter out SSO username
 * apikey_pub - to filter out public SSO key
 * secret - to filter out SSO secret
 * access_token - to filter out SSO access tokens
 * authid - to filter out auth username
 * authpassword - to filter out auth password
 * x-newrelic-id - to filter out newRelic ID

### 2.3.5 > 2.3.6
* Run a range of consolidation destroying SQL:

```sql
DELETE FROM `migrations`
WHERE `migration` LIKE '0000_00_00_000000_consolidation_%'
LIMIT 1;

UPDATE `migrations`
SET `migration` = '2015_01_01_000000_initial_mship'
WHERE `migration` = '2015_01_01_000000_vanilla_mship_v224'
LIMIT 1;

UPDATE `migrations`
SET `migration` = '2015_01_01_000010_initial_system'
WHERE `migration` = '2015_01_01_000010_vanilla_system_v221'
LIMIT 1;

UPDATE `migrations`
SET `migration` = '2015_01_01_000020_initial_sso'
WHERE `migration` = '2015_01_01_000020_vanilla_sso_v221'
LIMIT 1;

UPDATE `migrations`
SET `migration` = '2015_01_01_000030_initial_teamspeak'
WHERE `migration` = '2015_01_01_000030_vanilla_teamspeak_v221'
LIMIT 1;

UPDATE `migrations`
SET `migration` = '2015_01_01_000040_initial_messages'
WHERE `migration` = '2015_01_01_000040_vanilla_messages_v221'
LIMIT 1;

UPDATE `migrations`
SET `migration` = '2015_01_01_000050_initial_statistic'
WHERE `migration` = '2015_01_01_000050_vanilla_statistic_v224'
LIMIT 1;
```

* Run `composer dumpautoload`
* Delete `storage/app/modules.json`
 * Run `php artisan optimize && php artisan module:optimize && php artisan route:clear`

### 2.3.4 > 2.3.5
* Remove/reset storage/app/modules.json

### 2.3.3 > 2.3.4
* Run `php artisan migrate --step -vvv`
* Increase the frequency of `php artisan Member:CertImport` to every hour
* Add cronjob for `php artisan Member:CertImport --full` for midnight
 * It cannot be on the same offset as the first job.  Ideally, just after.

### 2.3.3 > 2.3.4
 * Run `php artisan module:migrate visittransfer -vvv`
 * Modify cronjob for visittransfer:cleanup to run every minute
 * Run `gulp --production`

### 2.3.2 > 2.3.3
* Run `composer update -o --no-dev`
* Run `php artisan visittransfer:statistics:daily 2016-08-22 yesterday -vvv`
* Run `gulp --production`

### 2.3.1 > 2.3.2
* Run `composer install -o --no-dev`
* Run `php artisan module:migrate visittransfer -vvv`
* Run `php artisan visittransfer:statistics:daily 2016-08-22 yesterday -vvv`
* Run `gulp --production`

### 2.3.0 > 2.3.1
* Run `composer update -o --no-dev`

### 2.2.4 > 2.3.0
* Run `composer install -o --no-dev`
* Run `php artisan module:optimize`
* Run `php artisan module:migrate`
* Enable vt.core as another host for visiting/transferring

### 2.2.3 > 2.2.4
* Run `php artisan teaman:map -v` to map TeamSpeak groups/channels to the database.
* Check the results of the above, and correct/add any further changes to database tables.

### 2.2.2 > 2.2.3

* Modify the migrations table:

* * renamed:    database/migrations/2015_12_27_221900_vanilla_mship_v221.php -> database/migrations/2015_01_01_000000_vanilla_mship_v221.php
* * renamed:    database/migrations/2015_12_27_221901_vanilla_system_v221.php -> database/migrations/2015_01_01_000010_vanilla_system_v221.php
* * renamed:    database/migrations/2015_12_27_221902_vanilla_sso_v221.php -> database/migrations/2015_01_01_000020_vanilla_sso_v221.php
* * renamed:    database/migrations/2015_12_27_221903_vanilla_teamspeak_v221.php -> database/migrations/2015_01_01_000030_vanilla_teamspeak_v221.php
* * renamed:    database/migrations/2015_12_27_221904_vanilla_messages_v221.php -> database/migrations/2015_01_01_000040_vanilla_messages_v221.php

```sql
UPDATE `migrations`
SET `migration` = '2015_01_01_000000_vanilla_mship_v221'
WHERE `migration` = '2015_12_27_221900_vanilla_mship_v221';

UPDATE `migrations`
SET `migration` = '2015_01_01_000010_vanilla_system_v221'
WHERE `migration` = '2015_12_27_221901_vanilla_system_v221';

UPDATE `migrations`
SET `migration` = '2015_01_01_000020_vanilla_sso_v221'
WHERE `migration` = '2015_12_27_221902_vanilla_sso_v221';

UPDATE `migrations`
SET `migration` = '2015_01_01_000030_vanilla_teamspeak_v221'
WHERE `migration` = '2015_12_27_221903_vanilla_teamspeak_v221';

UPDATE `migrations`
SET `migration` = '2015_01_01_000040_vanilla_messages_v221'
WHERE `migration` = '2015_12_27_221904_vanilla_messages_v221';
```

* Active the slack:manager cronjob:
* * `php artisan slack:manager` every 2 minutes should suffice for now.

* Re-run all statistics: `php artisan sys:statistics:daily 2001-01-01`

### 2.2.1 > 2.2.2
* Enable the stats tracking cronjobs:
* * `statistics:download`

* Modify the cronjob from `Statistics:daily`
* * Set as `php artisan sys:statistics:daily`

* Modify the migrations table *BEFORE DEPLOYMENT*
* * [Update] `2014_09_12_200312_initial_membership` => `2015_12_27_221900_vanilla_mship_v221`
* * [Update] `2014_09_12_200319_initial_system` => `2015_12_27_221901_vanilla_system_v221`
* * [Update] `2014_09_12_200323_initial_sso` => `2015_12_27_221902_vanilla_sso_v221`
* * [Update] `2014_09_22_203120_system_postmaster_initial` => `2015_12_27_221904_vanilla_messages_v221`
* * [Delete] `2014_09_14_200323_database_security_types`
* * [Delete] `2014_09_14_200325_database_qualifications`
* * [Delete] `2014_09_20_200325_system_tokens`
* * [Delete] `2014_09_21_200325_database_sessions`
* * [Delete] `2014_09_21_200326_invisible_accounts`
* * [Delete] `2014_09_21_200327_extra_vatsim_mship_data`
* * [Delete] `2015_01_18_191900_mship_emails_verified_at`
* * [Delete] `2015_02_13_173800_data_changes_tracking`
* * [Delete] `2015_02_21_130300_mship_roles_permissions`
* * [Delete] `2015_02_22_134200_remove_settings`
* * [Delete] `2015_02_22_150600_mship_account_remember_token`
* * [Delete] `2015_02_22_152100_mship_account_auth_info`
* * [Delete] `2015_02_27_214000_upgrade_v13_v20`
* * [Delete] `2015_02_27_225900_upgrade_v20_v201`
* * [Delete] `2015_03_02_213400_upgrade_v2005_v206`
* * [Delete] `2015_03_03_230600_upgrade_v2011_v2012`
* * [Update] `2015_02_28_224937_teamspeak_database_structure, batch=6` => `2015_12_27_221903_vanilla_teamspeak_v221`, batch=3
* * [Delete] `2015_03_11_012148_teamspeak_permissions`
* * [Update] `2015_05_03_204155_short_url, batch=7` => `2015_05_03_204155_short_url`, batch=4
* * [Delete] `2015_05_17_183500_upgrade_v2109_v2110`
* * [Delete] `2015_05_31_132800_upgrade_v2110_v2111`
* * [Update] `2015_06_24_202137_staff_management, batch=10` => `2015_06_24_202137_staff_management`, batch=5
* * [Update] `2015_06_24_205753_staff_management_data, batch=10` => `2015_06_24_205753_staff_management_data`, batch=5
* * [Delete] `2015_08_24_224100_account_bans`
* * [Delete] `2015_08_29_174200_postmaster_templates_removal`
* * [Update] `2015_08_29_203828_create_jobs_table, batch=10` => `2015_08_29_203828_create_jobs_table`, batch=6
* * [Update] `2015_08_29_204330_create_failed_jobs_table, batch=10` => `2015_08_29_204330_create_failed_jobs_table`, batch=6

```sql
UPDATE migrations
SET migration = '2015_12_27_221900_vanilla_mship_v221'
WHERE migration = '2014_09_12_200312_initial_membership';

UPDATE migrations
SET migration = '2015_12_27_221901_vanilla_system_v221'
WHERE migration = '2014_09_12_200319_initial_system';

UPDATE migrations
SET migration = '2015_12_27_221902_vanilla_sso_v221'
WHERE migration = '2014_09_12_200323_initial_sso';

UPDATE migrations
SET migration = '2015_12_27_221904_vanilla_messages_v221'
WHERE migration = '2014_09_22_203120_system_postmaster_initial';

DELETE FROM migrations WHERE migration IN ('2015_08_29_174200_postmaster_templates_removal','2015_08_24_224100_account_bans','2015_05_31_132800_upgrade_v2110_v2111','2015_05_17_183500_upgrade_v2109_v2110','2015_03_11_012148_teamspeak_permissions','2015_03_03_230600_upgrade_v2011_v2012','2015_03_02_213400_upgrade_v2005_v206','2015_02_27_225900_upgrade_v20_v201','2015_02_27_214000_upgrade_v13_v20','2015_02_22_152100_mship_account_auth_info','2015_02_22_150600_mship_account_remember_token','2015_02_22_134200_remove_settings','2015_02_21_130300_mship_roles_permissions','2015_02_13_173800_data_changes_tracking','2015_01_18_191900_mship_emails_verified_at','2014_09_21_200327_extra_vatsim_mship_data','2014_09_21_200326_invisible_accounts','2014_09_21_200325_database_sessions','2014_09_20_200325_system_tokens','2014_09_14_200325_database_qualifications','2014_09_14_200323_database_security_types');

UPDATE migrations
SET migration = '2015_12_27_221903_vanilla_teamspeak_v221', batch = '3'
WHERE migration = '2015_02_28_224937_teamspeak_database_structure';

UPDATE migrations
SET migration = '2015_05_03_204155_short_url', batch = '4'
WHERE migration = '2015_05_03_204155_short_url';

UPDATE migrations
SET migration = '2015_06_24_202137_staff_management', batch = '5'
WHERE migration = '2015_06_24_202137_staff_management';

UPDATE migrations
SET migration = '2015_06_24_205753_staff_management_data', batch = '5'
WHERE migration = '2015_06_24_205753_staff_management_data';

UPDATE migrations
SET migration = '2015_08_29_204330_create_failed_jobs_table', batch = '6'
WHERE migration = '2015_08_29_204330_create_failed_jobs_table';

UPDATE migrations
SET migration = '2015_08_29_203828_create_jobs_table', batch = '6'
WHERE migration = '2015_08_29_203828_create_jobs_table';
```

* Set each role's session_timeout after migrating

### 2.2.0 > 2.2.1
* Enable the SyncMentors cronjob: <NF has the name>
