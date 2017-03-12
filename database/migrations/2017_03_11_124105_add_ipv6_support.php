<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIpv6Support extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
        MSHIP_ACCOUNT
       */

        // Due to issue with ENUM's being in the mship_account table, we have to use the query builder to rename the column
        DB::statement("ALTER TABLE `mship_account` CHANGE `last_login_ip` `last_login_ip_old` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0'");

        Schema::table('mship_account', function (Blueprint $table) {
            $table->string('last_login_ip', 45)->after('last_login_ip_old')->default('0.0.0.0');
        });

        $this->convertOldIPsToNew('mship_account', 'last_login_ip_old', 'last_login_ip');

        Schema::table('mship_account', function (Blueprint $table) {
            $table->dropColumn('last_login_ip_old');
        });

          /*
          SYS_ACTIVITY
         */

        Schema::table('sys_activity', function (Blueprint $table) {
            $table->string('ip_new', 45)->after('ip')->default('0.0.0.0');
        });

        $this->convertOldIPsToNew('sys_activity', 'ip', 'ip_new');

        Schema::table('sys_activity', function (Blueprint $table) {
            $table->dropColumn('ip');
            $table->renameColumn('ip_new', 'ip');
        });

         /*
          SYS_SESSIONS - Not doing too much to this as it is not implemented at the moment
        */

        Schema::table('sys_sessions', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable(false)->default('0.0.0.0')->change();
        });

        /*
          SYS_TIMELINE_ENTRY
        */

        Schema::table('sys_timeline_entry', function (Blueprint $table) {
            $table->string('ip_new', 45)->after('ip')->default('0.0.0.0');
        });

        $this->convertOldIPsToNew('sys_timeline_entry', 'ip', 'ip_new');

        Schema::table('sys_timeline_entry', function (Blueprint $table) {
            $table->dropColumn('ip');
            $table->renameColumn('ip_new', 'ip');
        });

        /*
          TEAMSPEAK_REGISTRATION
        */

        Schema::table('teamspeak_registration', function (Blueprint $table) {
            $table->string('registration_ip_new', 45)->after('registration_ip')->default('0.0.0.0');
            $table->string('last_ip_new', 45)->after('last_ip')->default('0.0.0.0');
        });

        $this->convertOldIPsToNew('teamspeak_registration', 'registration_ip', 'registration_ip_new');
        $this->convertOldIPsToNew('teamspeak_registration', 'last_ip', 'last_ip_new');

        Schema::table('teamspeak_registration', function (Blueprint $table) {
            $table->dropColumn('registration_ip');
            $table->dropColumn('last_ip');
            $table->renameColumn('registration_ip_new', 'registration_ip');
            $table->renameColumn('last_ip_new', 'last_ip');
        });
    }

    public function convertOldIPsToNew($table, $oldcolumn, $newcolumn)
    {
        DB::table($table)->update([$newcolumn => DB::raw('INET_NTOA(`'.$oldcolumn.'`)')]);
    }

    public function convertNewIPsToOld($table, $oldcolumn, $newcolumn)
    {
        DB::table($table)->update([$oldcolumn => DB::raw('INET_ATON(`'.$newcolumn.'`)')]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // These rollback migrations WILL NOT WORK if there are IPv6 addresses in database already

        /*
        MSHIP_ACCOUNT
        */

        Schema::table('mship_account', function (Blueprint $table) {
            $table->bigInteger('last_login_ip_old')->after('last_login_ip')->unsigned()->default('0');
        });

        $this->convertNewIPsToOld('mship_account', 'last_login_ip_old', 'last_login_ip');

        Schema::table('mship_account', function (Blueprint $table) {
            $table->dropColumn('last_login_ip');
        });

        DB::statement("ALTER TABLE `mship_account` CHANGE `last_login_ip_old` `last_login_ip` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0'");

        /*
        SYS_ACTIVITY
        */

        Schema::table('sys_activity', function (Blueprint $table) {
            $table->bigInteger('ip_old')->after('ip')->unsigned()->default('0');
        });

        $this->convertNewIPsToOld('sys_activity', 'ip_old', 'ip');

        Schema::table('sys_activity', function (Blueprint $table) {
            $table->dropColumn('ip');
            $table->renameColumn('ip_old', 'ip');
        });

        /*
          SYS_SESSIONS - Not doing too much to this as it is not implemented at the moment
        */
        Schema::table('sys_sessions', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->default(null)->change();
        });

        /*
        SYS_TIMELINE_ENTRY
        */

        Schema::table('sys_timeline_entry', function (Blueprint $table) {
            $table->bigInteger('ip_old')->after('ip')->unsigned()->default('0');
        });

        $this->convertNewIPsToOld('sys_timeline_entry', 'ip_old', 'ip');

        Schema::table('sys_timeline_entry', function (Blueprint $table) {
            $table->dropColumn('ip');
            $table->renameColumn('ip_old', 'ip');
        });

        /*
        TEAMSPEAK_REGISTRATION
        */

        Schema::table('teamspeak_registration', function (Blueprint $table) {
            $table->bigInteger('registration_ip_old')->after('registration_ip')->unsigned()->default('0');
            $table->bigInteger('last_ip_old')->after('last_ip')->unsigned()->nullable()->default('0');
        });

        $this->convertNewIPsToOld('teamspeak_registration', 'registration_ip_old', 'registration_ip');
        $this->convertNewIPsToOld('teamspeak_registration', 'last_ip_old', 'last_ip');

        Schema::table('teamspeak_registration', function (Blueprint $table) {
            $table->dropColumn('registration_ip');
            $table->dropColumn('last_ip');
            $table->renameColumn('registration_ip_old', 'registration_ip');
            $table->renameColumn('last_ip_old', 'last_ip');
        });
    }
}
