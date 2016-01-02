## Upgrade Notes

### Future
* Publish module files with:
* * `php artisan vendor:publish --force --provider="App\Modules\Statistics\StatisticsServiceProvider"`

* Enable the stats tracking cronjobs:
* * `statistics:download`

* Modify the cronjob from `Statistics:daily`
* * Set as `php artisan sys:statistics:daily`

* [Depends on feature/refactor_all_migrations] Modify the migrations table *BEFORE DEPLOYMENT*
* * [Update] 2014_09_12_200312_initial_membership => 2015_12_27_221900_vanilla_mship_v221
* * [Update] 2014_09_12_200319_initial_system => 2015_12_27_221901_vanilla_system_v221
* * [Update] 2014_09_12_200323_initial_sso => 2015_12_27_221902_vanilla_sso_v221
* * [Delete] 2014_09_14_200323_database_security_types
* * [Delete] 2014_09_14_200325_database_qualifications
* * [Delete] 2014_09_20_200325_system_tokens
* * [Delete] 2014_09_21_200325_database_sessions
* * [Delete] 2014_09_21_200326_invisible_accounts
* * [Delete] 2014_09_21_200327_extra_vatsim_mship_data
* * [Delete] 2014_09_22_203120_system_postmaster_initial
* * [Delete] 2015_01_18_191900_mship_emails_verified_at
* * [Delete] 2015_02_13_173800_data_changes_tracking
* * [Delete] 2015_02_21_130300_mship_roles_permissions
* * [Delete] 2015_02_22_134200_remove_settings
* * [Delete] 2015_02_22_150600_mship_account_remember_token
* * [Delete] 2015_02_22_152100_mship_account_auth_info
* * [Delete] 2015_02_27_214000_upgrade_v13_v20
* * [Delete] 2015_02_27_225900_upgrade_v20_v201
* * [Delete] 2015_03_02_213400_upgrade_v2005_v206
* * [Delete] 2015_03_03_230600_upgrade_v2011_v2012
* * [Update] 2015_02_28_224937_teamspeak_database_structure, batch=6 => 2015_12_27_221903_vanilla_teamspeak_v221, batch=3
* * [Delete] 2015_03_11_012148_teamspeak_permissions
* * [Update] 2015_05_03_204155_short_url, batch=7 => 2015_05_03_204155_short_url, batch=4
* * [Delete] 2015_05_17_183500_upgrade_v2109_v2110
* * [Delete] 2015_05_31_132800_upgrade_v2110_v2111
* * [Update] 2015_06_24_202137_staff_management, batch=10 => 2015_06_24_202137_staff_management, batch=5
* * [Update] 2015_06_24_205753_staff_management_data, batch=10 => 2015_06_24_205753_staff_management_data, batch=5
* * [Delete] 2015_08_24_224100_account_bans
* * [Delete] 2015_08_29_174200_postmaster_templates_removal
* * [Update] 2015_08_29_203828_create_jobs_table, batch=10 => 2015_08_29_203828_create_jobs_table, batch=6
* * [Update] 2015_08_29_204330_create_failed_jobs_table, batch=10 => 2015_08_29_204330_create_failed_jobs_table, batch=6

### 2.2.0 > 2.2.1
* Enable the SyncMentors cronjob: <NF has the name>