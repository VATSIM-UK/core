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
        Schema::create('training_position_waiting_list', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('training_position_id');
            $table->unsignedInteger('waiting_list_id');
            $table->timestamps();

            $table->foreign('training_position_id')
                ->references('id')
                ->on('training_positions')
                ->onDelete('cascade');

            $table->foreign('waiting_list_id')
                ->references('id')
                ->on('training_waiting_list')
                ->onDelete('cascade');

            $table->unique(['training_position_id', 'waiting_list_id'], 'tp_wl_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_position_waiting_list');
    }
};
