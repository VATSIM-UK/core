<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('mship_note_type')->insert([
            'name' => 'Roster',
            'short_code' => 'roster',
            'is_available' => true,
            'is_system' => false,
            'colour_code' => 'info',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('mship_note_type')->where('short_code', 'roster')->delete();
    }
};
