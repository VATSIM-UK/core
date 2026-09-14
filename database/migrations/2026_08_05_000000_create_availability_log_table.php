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
        Schema::create('availability_log', function (Blueprint $table) {
            $table->id();
            $table->ulid('training_place_id');
            $table->foreign('training_place_id')
                ->references('id')
                ->on('training_places')
                ->onDelete('cascade');
            $table->enum('event', ['added', 'merged', 'edited']);
            $table->dateTime('slot_from');
            $table->dateTime('slot_to');
            $table->timestamp('created_at');
            $table->timestamp('superseded_at')->nullable();
            $table->index(['training_place_id', 'created_at']);
            $table->index(['training_place_id', 'slot_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability_log');
    }
};
