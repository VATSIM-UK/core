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
        Schema::create('availability_checks', function (Blueprint $table) {
            $table->id();
            $table->ulid('training_place_id');
            $table->foreign('training_place_id')
                ->references('id')
                ->on('training_places')
                ->onDelete('cascade');
            $table->enum('status', ['passed', 'failed']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability_checks');
    }
};
