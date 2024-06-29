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
        Schema::table('discord_role_rules', function (Blueprint $table) {
            $table->nullableMorphs('endorsable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discord_role_rules', function (Blueprint $table) {
            $table->dropMorphs('endorsable');
        });
    }
};
