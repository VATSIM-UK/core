<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function () {
            $parPositionGroupId = DB::table('position_groups')
                ->where('name', 'Military (PAR)')
                ->value('id');

            $milAppPositionGroupId = DB::table('position_groups')
                ->where('name', 'Military (APP)')
                ->value('id');

            // Convert PAR to MIL APP endorsements
            DB::table('mship_account_endorsement')
                ->where('endorsable_type', 'App\Models\Atc\PositionGroup')
                ->where('endorsable_id', $parPositionGroupId)
                ->update(['endorsable_id' => $milAppPositionGroupId]);

            DB::table('position_group_positions')
                ->where('position_group_id', $parPositionGroupId)
                ->delete();

            DB::table('position_groups')
                ->where('id', $parPositionGroupId)
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
