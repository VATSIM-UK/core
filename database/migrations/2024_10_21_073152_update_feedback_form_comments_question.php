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
        DB::statement('UPDATE mship_feedback_questions
            SET question="Do you have any further comments? Please make comments detailed, specific and avoid identifying yourself"
            WHERE form_id IN (1,2) and slug="report9"');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
