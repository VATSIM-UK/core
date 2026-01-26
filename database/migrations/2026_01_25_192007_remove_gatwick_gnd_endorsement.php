<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function () {
            $gatwickPositionGroupId = DB::table('position_groups')
                ->where('name', 'Gatwick S1 (DEL/GND)')
                ->value('id');

            DB::table('mship_account_endorsement')
                ->where('endorsable_type', 'App\Models\Atc\PositionGroup')
                ->where('endorsable_id', $gatwickPositionGroupId)
                ->delete();

            DB::table('position_group_conditions')
                ->where('position_group_id', $gatwickPositionGroupId)
                ->delete();

            DB::table('position_group_positions')
                ->where('position_group_id', $gatwickPositionGroupId)
                ->delete();

            DB::table('position_group')
                ->where('id', $gatwickPositionGroupId)
                ->delete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
