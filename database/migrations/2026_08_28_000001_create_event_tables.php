<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tagline')->nullable();
            $table->longText('description')->nullable();
            $table->string('image_url')->nullable();
            $table->dateTime('start');
            $table->dateTime('end');
            $table->boolean('rostered')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('published_by')->nullable();
            $table->foreign('published_by')->references('id')->on('mship_account')->nullOnDelete();
            $table->unsignedInteger('manager_id')->nullable();
            $table->foreign('manager_id')->references('id')->on('mship_account')->nullOnDelete();
            $table->timestamps();

            $table->index(['published_at', 'start']);
        });

        Schema::create('event_positions', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id');
            $table->unsignedInteger('position_id');
            $table->primary(['event_id', 'position_id']);
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
            $table->foreign('position_id')->references('id')->on('positions')->cascadeOnDelete();
        });

        Schema::create('event_checklist_completions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->string('item', 64);
            $table->unsignedInteger('account_id')->nullable();
            $table->timestamp('completed_at');

            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
            $table->foreign('account_id')->references('id')->on('mship_account')->nullOnDelete();
            $table->unique(['event_id', 'item']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_checklist_completions');
        Schema::dropIfExists('event_positions');
        Schema::dropIfExists('events');
    }
};
