<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vt_facility', function (Blueprint $table) {
            $table->boolean('skip_90_day_check')->default(false)->after('stage_checks');
            $table->boolean('skip_50_hours_check')->default(false)->after('skip_90_day_check');
        });
    }

    public function down(): void
    {
        Schema::table('vt_facility', function (Blueprint $table) {
            $table->dropColumn(['skip_90_day_check', 'skip_50_hours_check']);
        });
    }
};