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
        Schema::create('availability_warnings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('training_place_id');
            $table->foreign('training_place_id')
                ->references('id')
                ->on('training_places')
                ->onDelete('cascade');
            $table->unsignedBigInteger('availability_check_id');
            $table->foreign('availability_check_id')
                ->references('id')
                ->on('availability_checks')
                ->onDelete('cascade');
            $table->enum('status', ['pending', 'resolved', 'expired']);
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_availability_check_id')->nullable();
            $table->foreign('resolved_availability_check_id')
                ->references('id')
                ->on('availability_checks')
                ->onDelete('set null');
            $table->timestamp('removal_actioned_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability_warnings');
    }
};
