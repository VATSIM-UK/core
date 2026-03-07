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
            $table->unsignedInteger('required_endorsement_id')->nullable()->after('retention_checks_months');
            $table->foreign('required_endorsement_id')->references('id')->on('position_groups')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_waiting_list', function (Blueprint $table) {
            $table->dropForeign(['required_endorsement_id']);
            $table->dropColumn('required_endorsement_id');
        });
    }
};
