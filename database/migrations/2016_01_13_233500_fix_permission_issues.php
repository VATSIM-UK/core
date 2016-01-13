<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixPermissionIssues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table("mship_permission")
          ->where("name", "=", "adm/mship/account/*/ban/edit")
          ->update([
              "name"         => "adm/mship/ban/*/modify",
              "updated_at"   => \Carbon\Carbon::now()
          ]);

        DB::table("mship_permission")
          ->where("name", "=", "adm/mship/account/*/ban/repeal")
          ->update([
              "name"         => "adm/mship/ban/*/repeal",
              "updated_at"   => \Carbon\Carbon::now()
          ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table("mship_permission")
          ->where("name", "=", "adm/mship/ban/*/modify")
          ->update([
              "name"         => "adm/mship/account/*/ban/edit",
              "updated_at"   => \Carbon\Carbon::now()
          ]);

        DB::table("mship_permission")
          ->where("name", "=", "adm/mship/ban/*/repeal")
          ->update([
              "name"         => "adm/mship/account/*/ban/repeal",
              "updated_at"   => \Carbon\Carbon::now()
          ]);
    }
}
