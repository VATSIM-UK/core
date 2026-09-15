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
        Schema::table('training_waiting_list_flags', function (Blueprint $table) {
            $table->string('moodle_course_idnumber', 100)->nullable()->after('position_group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_waiting_list_flags', function (Blueprint $table) {
            $table->dropColumn('moodle_course_idnumber');
        });
    }
};
