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
        Schema::create('training_place_leave_of_absences', function (Blueprint $table) {
            $table->id();
            $table->string('training_place_id');
            $table->dateTime('begins_at');
            $table->dateTime('ends_at');
            $table->text('reason');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_place_leave_of_absences');
    }
};
