<?php /** @noinspection SpellCheckingInspection */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('mship_feedback_questions')
            ->where('slug', 'report9')
            ->update(['question' => 'Do you have any further comments? Please make comments detailed, specific and avoid identifying yourself']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
