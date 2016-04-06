<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConsolidationV224 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // This is legacy from 2.2.2 > 2.2.3 (but it's required for dev installs).
        DB::query("UPDATE `migrations`
                   SET `migration` = '2015_12_27_221900_vanilla_mship_v221'
                   WHERE `migration` = '2014_09_12_200312_initial_membership';

                   UPDATE `migrations`
                   SET `migration` = '2015_12_27_221901_vanilla_system_v221'
                   WHERE `migration` = '2014_09_12_200319_initial_system';

                   UPDATE `migrations`
                   SET `migration` = '2015_12_27_221902_vanilla_sso_v221'
                   WHERE `migration` = '2014_09_12_200323_initial_sso';

                   UPDATE `migrations`
                   SET `migration` = '2015_12_27_221904_vanilla_messages_v221'
                   WHERE `migration` = '2014_09_22_203120_system_postmaster_initial';

                   DELETE FROM migrations
                   WHERE `migration` IN (
                      '2015_08_29_174200_postmaster_templates_removal',
                      '2015_08_24_224100_account_bans',
                      '2015_05_31_132800_upgrade_v2110_v2111',
                      '2015_05_17_183500_upgrade_v2109_v2110',
                      '2015_03_11_012148_teamspeak_permissions',
                      '2015_03_03_230600_upgrade_v2011_v2012',
                      '2015_03_02_213400_upgrade_v2005_v206',
                      '2015_02_27_225900_upgrade_v20_v201',
                      '2015_02_27_214000_upgrade_v13_v20',
                      '2015_02_22_152100_mship_account_auth_info',
                      '2015_02_22_150600_mship_account_remember_token',
                      '2015_02_22_134200_remove_settings',
                      '2015_02_21_130300_mship_roles_permissions',
                      '2015_02_13_173800_data_changes_tracking',
                      '2015_01_18_191900_mship_emails_verified_at',
                      '2014_09_21_200327_extra_vatsim_mship_data',
                      '2014_09_21_200326_invisible_accounts',
                      '2014_09_21_200325_database_sessions',
                      '2014_09_20_200325_system_tokens',
                      '2014_09_14_200325_database_qualifications',
                      '2014_09_14_200323_database_security_types');

                   UPDATE `migrations`
                   SET `migration` = '2015_12_27_221903_vanilla_teamspeak_v221', batch = '3'
                   WHERE `migration` = '2015_02_28_224937_teamspeak_database_structure';

                   UPDATE `migrations`
                   SET `migration` = '2015_05_03_204155_short_url', batch = '4'
                   WHERE `migration` = '2015_05_03_204155_short_url';

                   UPDATE `migrations`
                   SET `migration` = '2015_06_24_202137_staff_management', batch = '5'
                   WHERE `migration` = '2015_06_24_202137_staff_management';

                   UPDATE `migrations`
                   SET `migration` = '2015_06_24_205753_staff_management_data', batch = '5'
                   WHERE `migration` = '2015_06_24_205753_staff_management_data';

                   UPDATE `migrations`
                   SET `migration` = '2015_08_29_204330_create_failed_jobs_table', batch = '6'
                   WHERE `migration` = '2015_08_29_204330_create_failed_jobs_table';

                   UPDATE `migrations`
                   SET `migration` = '2015_08_29_203828_create_jobs_table', batch = '6'
                   WHERE `migration` = '2015_08_29_203828_create_jobs_table';

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
                   WHERE `migration` = '2015_12_27_221904_vanilla_messages_v221';");

        /** What new changes do we have in store for 2.2.4? */
        DB::statement("UPDATE `migrations`
                   SET `migration` = '2015_01_01_000050_vanilla_statistic_v224'
                   WHERE `migration` = '2015_01_16_224400_statistics_implementation'");

        DB::statement("UPDATE `migrations`
                   SET `migration` = '2015_01_01_000000_vanilla_mship_v224'
                   WHERE `migration` = '2015_01_01_000000_vanilla_mship_v221'");

        DB::statement("DELETE FROM `migrations`
                   WHERE `migration` = '2015_12_30_231000_acp_system_bans'");

        DB::statement("DELETE FROM `migrations`
                   WHERE `migration` = '2015_12_23_160400_create_atc_statistics_tables'");

        DB::statement("DELETE FROM `migrations`
                   WHERE `migration` = '2016_01_04_234000_ban_reason_improvements'");

        DB::statement("DELETE FROM `migrations`
                   WHERE `migration` = '2016_01_07_234041_account_security_improvements'");

        DB::statement("DELETE FROM `migrations`
                   WHERE `migration` = '2016_01_08_172516_fix_remember_token'");

        DB::statement("DELETE FROM `migrations`
                   WHERE `migration` = '2016_01_10_183700_repeal_bans'");

        DB::statement("DELETE FROM `migrations`
                   WHERE `migration` = '2016_01_10_184500_modify_ban_reverse_permission'");

        DB::statement("DELETE FROM `migrations`
                   WHERE `migration` = '2016_01_12_110337_roles_add_session_expiry'");

        DB::statement("DELETE FROM `migrations`
                   WHERE `migration` = '2016_01_12_133530_remove_auth_extra'");

        DB::statement("DELETE FROM `migrations`
                   WHERE `migration` = '2016_01_13_180900_remove_ban_period'");

        DB::statement("DELETE FROM `migrations`
                   WHERE `migration` = '2016_01_13_233500_fix_permission_issues'");

        DB::statement("DELETE FROM `migrations`
                   WHERE `migration` = '2016_01_14_130714_add_qualification_6'");

        DB::statement("DELETE FROM `migrations`
                   WHERE `migration` = '2016_02_01_005211_add_slack_id_to_account'");

        /**
         * Queries below are a range of data changes, including:
         *
         * - zero timestamps, to null (Mysql 5.7)
         * - empty string, to null
         */
        DB::update('UPDATE mship_account SET remember_token = NULL WHERE remember_token = ""');
        DB::update('UPDATE mship_account_security SET expires_at = NULL WHERE expires_at = \'0000-00-00 00:00:00\'');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Sadly, there's no coming back from this as the previous DB may be in *any* state.
    }
}
