<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_availability_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('account_id');
            $table->time('from');
            $table->time('to');
            $table->timestamps();

            $table->unique('account_id');
            $table->foreign('account_id')->references('id')->on('mship_account')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_availability_settings');
    }
};
