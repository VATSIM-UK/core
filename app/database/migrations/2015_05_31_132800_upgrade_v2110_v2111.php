<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpgradeV2110V2111 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        // Modify the TS log enum type.
        // This is a clunky way of doing it, but DBAL doesn't support changes to enums using the inbuilt methods.
        // We have to use this to get rid of the enum and make it VC.
        Schema::table("teamspeak_log", function($table){
            DB::statement('ALTER TABLE `teamspeak_log` CHANGE `type` `type` VARCHAR(75)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
    }
}