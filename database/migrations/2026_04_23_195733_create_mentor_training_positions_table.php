<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentor_training_positions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('account_id');
            $table->foreignId('training_position_id')->constrained('training_positions')->cascadeOnDelete();
            $table->unsignedInteger('created_by');
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('mship_account')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('mship_account');

            $table->unique(['account_id', 'training_position_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentor_training_positions');
    }
};
