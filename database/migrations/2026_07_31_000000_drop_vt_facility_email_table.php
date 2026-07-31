<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('vt_facility_email');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('vt_facility_email', function ($table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('facility_id');
            $table->string('email');
            $table->timestamps();

            $table->foreign('facility_id')->references('id')->on('vt_facility');
        });
    }
};
