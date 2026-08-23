<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('mship_feedback_question_types')->insert([
            'name' => 'position_selector',
            'code' => '<option value="%4$s" %5$s>%3$s</option>',
            'rules' => 'exists:positions,callsign',
            'max_uses' => 0,
            'requires_value' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('mship_feedback_question_types')->where('name', 'position_selector')->delete();
    }
};
