<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_positions', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id');
            $table->unsignedInteger('position_id');
            $table->primary(['event_id', 'position_id']);
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
            $table->foreign('position_id')->references('id')->on('positions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_positions');
    }
};
