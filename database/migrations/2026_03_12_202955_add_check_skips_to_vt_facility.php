<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vt_facility', function (Blueprint $table) {
            $table->boolean('enable_90_day_check')->default(true)->after('stage_checks');
            $table->boolean('enable_50_hours_check')->default(true)->after('enable_90_day_check');
        });
    }

    public function down(): void
    {
        Schema::table('vt_facility', function (Blueprint $table) {
            $table->dropColumn(['enable_90_day_check', 'enable_50_hours_check']);
        });
    }
};