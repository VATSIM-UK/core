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
        Schema::table('vt_facility', function (Blueprint $table) {
            $table->unsignedBigInteger('minimum_atc_qualification_id')->after('public')->nullable();
            $table->unsignedBigInteger('maximum_atc_qualification_id')->after('minimum_atc_qualification_id')->nullable();
            $table->unsignedBigInteger('minimum_pilot_qualification_id')->after('minimum_atc_qualification_id')->nullable();
            $table->unsignedBigInteger('maximum_pilot_qualification_id')->after('minimum_pilot_qualification_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vt_facility', function (Blueprint $table) {
            $table->dropColumn(['minimum_atc_qualification_id', 'maximum_atc_qualification_id', 'minimum_pilot_qualification_id', 'maximum_pilot_qualification_id']);
        });
    }
};
