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
        Schema::table('training_waiting_list', function (Blueprint $table) {
            $table->unsignedInteger('self_enrolment_hours_at_qualification_id')->nullable();
            $table->unsignedInteger('self_enrolment_hours_at_qualification_minimum_hours')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_waiting_list', function (Blueprint $table) {
            $table->dropColumn('self_enrolment_hours_at_qualification_id');
            $table->dropColumn('self_enrolment_hours_at_qualification_minimum_hours');
        });
    }
};
