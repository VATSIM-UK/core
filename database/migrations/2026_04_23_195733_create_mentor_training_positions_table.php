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
            $table->string('mentorable_type');
            $table->unsignedBigInteger('mentorable_id');
            $table->unsignedInteger('created_by');
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('mship_account')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('mship_account');

            $table->index(['mentorable_type', 'mentorable_id'], 'mentor_tp_mentorable_index');
            $table->unique(['account_id', 'mentorable_type', 'mentorable_id'], 'mentor_tp_account_mentorable_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentor_training_positions');
    }
};
