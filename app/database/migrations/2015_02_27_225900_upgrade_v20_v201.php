<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpgradeV20V201 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::table("mship_permission")->insert(array(
            ["name" => "adm/mship/role/default", "display_name" => "Admin / Membership / Roles / Set Default", "created_at" => DB::raw("NOW()"), "updated_at" => DB::raw("NOW()")],
        ));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::table("mship_permission")
          ->where("name", "=", "/adm/mship/role/default")
          ->delete();
    }
}
