<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;

class AddMissingAdminMembershipAccountListFilterPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mship_permission')->insert([
            ['name' => 'adm/mship/account/all', 'display_name' => 'Admin / Membership / Account / Filter: All', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/active', 'display_name' => 'Admin / Membership / Account / Filter: Active', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/division', 'display_name' => 'Admin / Membership / Account / Filter: Division', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'adm/mship/account/nondivision', 'display_name' => 'Admin / Membership / Account / Filter: Non-Division', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('mship_permission')->whereIn('name', [
            'adm/mship/account/all',
            'adm/mship/account/active',
            'adm/mship/account/division',
            'adm/mship/account/nondivision',
        ])->delete();
    }
}
