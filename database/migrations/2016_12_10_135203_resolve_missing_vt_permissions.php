<?php

use Illuminate\Database\Migrations\Migration;

class ResolveMissingVtPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mship_permission')
          ->where('name', '=', 'adm/visit-transfer/create')
          ->update(['name' => 'adm/visit-transfer/facility/create']);

        $checkPermission = DB::table('mship_permission')
                             ->where('name', '=', 'adm/visit-transfer/facility/*/update')
                             ->count() > 0;
        if ($checkPermission) {
            DB::table('mship_permission')
              ->insert([
                  [
                      'name' => 'adm/visit-trasnfer/facility/*/update',
                      'display_name' => 'Admin / Visit &amp; Transfer / Facility / Update',
                      'created_at' => \Carbon\Carbon::now(),
                      'updated_at' => \Carbon\Carbon::now(),
                  ],
              ]);
        }

        $checkPermission = DB::table('mship_permission')
                             ->where('name', '=', 'adm/visit-transfer/facility/*/check/met')
                             ->count() > 0;
        if ($checkPermission) {
            DB::table('mship_permission')
              ->insert([
                  [
                      'name' => 'adm/visit-trasnfer/facility/*/check/met',
                      'display_name' => 'Admin / Visit &amp; Transfer / Facility / Check / Met',
                      'created_at' => \Carbon\Carbon::now(),
                      'updated_at' => \Carbon\Carbon::now(),
                  ],
              ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('mship_permission')
          ->where('name', '=', 'adm/visit-transfer/facility/create')
          ->update(['name' => 'adm/visit-transfer/create']);

        DB::table('mship_permission')
          ->where('name', '=', 'adm/visit-transfer/facility/*/update')
          ->delete();

        DB::table('mship_permission')
          ->where('name', '=', 'adm/visit-transfer/facility/*/check/met')
          ->delete();
    }
}
