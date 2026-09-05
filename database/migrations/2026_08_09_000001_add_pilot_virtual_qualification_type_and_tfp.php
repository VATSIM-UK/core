<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE mship_qualification MODIFY COLUMN type ENUM('pilot','pilot_military','atc','training_atc','training_pilot','admin','pilot_virtual') NOT NULL");

        DB::table('mship_qualification')->insertOrIgnore([
            'code' => 'TFP',
            'type' => 'pilot_virtual',
            'name_small' => 'TFP',
            'name_long' => 'The Flying Programme',
            'name_grp' => 'The Flying Programme',
            'vatsim' => 0,
        ]);
    }

    public function down(): void
    {
        DB::table('mship_qualification')->where('code', 'TFP')->where('type', 'pilot_virtual')->delete();

        DB::statement("ALTER TABLE mship_qualification MODIFY COLUMN type ENUM('pilot','pilot_military','atc','training_atc','training_pilot','admin') NOT NULL");
    }
};
