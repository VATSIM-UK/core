<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyBanReversePermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table("mship_permission")
          ->where("name", "=", "adm/mship/account/*/ban/reverse")
          ->update([
              "name"         => "adm/mship/account/*/ban/repeal",
              "display_name" => "Admin / Membership / Account / Ban / Repeal",
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
          ->where("name", "=", "adm/mship/account/*/ban/repeal")
          ->update([
              "name"         => "adm/mship/account/*/ban/reverse",
              "display_name" => "Admin / Membership / Account / Ban / Reverse",
              "updated_at"   => \Carbon\Carbon::now()
          ]);
    }
}
