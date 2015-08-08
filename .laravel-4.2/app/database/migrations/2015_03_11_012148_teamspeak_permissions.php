<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TeamspeakPermissions extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        DB::table("mship_permission")->insert(array(
            ["name" => "teamspeak/serveradmin", "display_name" => "TeamSpeak / Server Admin", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "teamspeak/idle/extended", "display_name" => "TeamSpeak / Extended Idle", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
            ["name" => "teamspeak/idle/permanent", "display_name" => "TeamSpeak / Permanent Idle", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
        ));

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        DB::table("mship_permission")
          ->where("name", "=", "teamspeak/serveradmin")
          ->orWhere("name", "=", "teamspeak/idle/extended")
          ->orWhere("name", "=", "teamspeak/idle/permanent")
          ->delete();

    }

}
