<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MovePrimaryEmailToAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mship_account', function (Blueprint $table) {
            $table->string('email', 200)->nullable()->after('name_last');
        });

        DB::update('UPDATE `mship_account` AS `a`
                   SET `email` = (SELECT `email`
                                  FROM `mship_account_email` AS `b`
                                  WHERE `b`.`account_id` = `a`.`account_id`
                                    AND `b`.`is_primary` = 1
                                    AND `b`.`deleted_at` IS NULL
                                  LIMIT 1)');

        DB::delete('DELETE FROM `mship_account_email` WHERE `is_primary` = 1');
        DB::delete('DELETE FROM `mship_account_email` WHERE `deleted_at` IS NOT NULL');

        DB::delete('DELETE FROM `mship_account_email` WHERE `deleted_at` IS NOT NULL');
        Schema::table('mship_account_email', function (Blueprint $table) {
            $table->dropColumn('is_primary');
            $table->dropColumn('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mship_account', function (Blueprint $table) {
            $table->dropColumn('email');
        });
        Schema::table('mship_account_email', function (Blueprint $table) {
            $table->boolean('is_primary')->before('verified_at')->default(0);
            $table->softDeletes();
        });
    }
}
