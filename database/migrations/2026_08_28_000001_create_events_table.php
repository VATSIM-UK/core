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
            $table->unsignedInteger('manager_id')->nullable();
            $table->foreign('manager_id')->references('id')->on('mship_account')->nullOnDelete();
            $table->boolean('eoi_published')->default(false);
            $table->boolean('roster_published')->default(false);
            $table->boolean('briefing_published')->default(false);
            $table->boolean('briefing_created')->default(false);
            $table->boolean('banner_created')->default(false);
            $table->boolean('ecfmp_set_up')->default(false);
            $table->boolean('my_vatsim_published')->default(false);
            $table->timestamps();

            $table->index(['published_at', 'start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
