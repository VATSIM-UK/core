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
        Schema::table('training_waiting_list_account', function (Blueprint $table) {
            $table->unsignedInteger('removed_by')->nullable();
            $table->text('removal_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_waiting_list_account', function (Blueprint $table) {
            $table->dropColumn('removed_by');
            $table->dropColumn('removal_reason');
        });
    }
};
