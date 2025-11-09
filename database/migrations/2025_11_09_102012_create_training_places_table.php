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
        Schema::create('training_positions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->unsignedInteger('position_id')->nullable()->after('id');
            $table->json('cts_positions')->nullable();
            $table->timestamps();

            $table->foreign('position_id')
                ->references('id')
                ->on('positions')
                ->onDelete('set null');
        });

        Schema::create('training_places', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->unsignedInteger('waiting_list_account_id')->nullable()->after('id');
            $table->ulid('training_position_id')->nullable()->after('waiting_list_account_id');
            $table->timestamps();

            $table->foreign('waiting_list_account_id')
                ->references('id')
                ->on('training_waiting_list_account')
                ->onDelete('set null');

            $table->foreign('training_position_id')
                ->references('id')
                ->on('training_positions')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_places');
        Schema::dropIfExists('training_positions');
    }
};
