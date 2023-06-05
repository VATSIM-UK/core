<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mship_permission')->insert(
            [
                [
                    'name' => 'discord/heathrow-endorsed-ground',
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'discord/heathrow-endorsed-tower',
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'discord/heathrow-endorsed-approach',
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]
        );

        DB::table('mship_role')->insert(
            [
                [
                    'name' => 'Heathrow Endorsed Ground',
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Heathrow Endorsed Tower',
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Heathrow Endorsed Approach',
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]
        );

        DB::table('mship_role_permission')->insert(
            [
                [
                    'permission_id' => DB::table('mship_permission')->where('name', 'discord/heathrow-endorsed-ground')->first()->id,
                    'role_id' => DB::table('mship_role')->where('name', 'Heathrow Endorsed Ground')->first()->id,
                ],
                [
                    'permission_id' => DB::table('mship_permission')->where('name', 'discord/heathrow-endorsed-tower')->first()->id,
                    'role_id' => DB::table('mship_role')->where('name', 'Heathrow Endorsed Tower')->first()->id,
                ],
                [
                    'permission_id' => DB::table('mship_permission')->where('name', 'discord/heathrow-endorsed-approach')->first()->id,
                    'role_id' => DB::table('mship_role')->where('name', 'Heathrow Endorsed Approach')->first()->id,
                ],
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('mship_role_permission')->whereIn('permission_id', DB::table('mship_permission')->where('name', 'like', 'discord/heathrow-endorsed-%')->pluck('id'))->delete();
        DB::table('mship_role')->whereIn('name', ['Heathrow Endorsed Ground', 'Heathrow Endorsed Tower', 'Heathrow Endorsed Approach'])->delete();
        DB::table('mship_permission')->whereIn('name', ['discord/heathrow-endorsed-ground', 'discord/heathrow-endorsed-tower', 'discord/heathrow-endorsed-approach'])->delete();
    }
};
