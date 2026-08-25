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
        Schema::table('mship_feedback_questions', function (Blueprint $table) {
            $table->integer('page')->after('sequence')->default('1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mship_feedback_questions', function (Blueprint $table) {
            $table->dropColumn('page');
        });
    }
};
