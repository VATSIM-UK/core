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
            $table->boolean('requires_roster_membership')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_waiting_list', function (Blueprint $table) {
            $table->dropColumn('requires_roster_membership');
        });
    }
};
