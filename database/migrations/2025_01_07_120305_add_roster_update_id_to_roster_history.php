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
        Schema::table('roster_history', function (Blueprint $table) {
            $table->tinyInteger('roster_update_id')->after('removed_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roster_history', function (Blueprint $table) {
            $table->dropColumn('roster_update_id');
        });
    }
};
