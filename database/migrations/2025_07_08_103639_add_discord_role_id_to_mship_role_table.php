<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mship_role', function (Blueprint $table) {
            $table->string('discord_role_id')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('mship_role', function (Blueprint $table) {
            $table->dropColumn('discord_role_id');
        });
    }
};
