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
        Schema::create('roster_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('account_id');
            $table->timestamp('original_created_at');
            $table->timestamp('original_updated_at');
            $table->unsignedInteger('removed_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roster_history');
    }
};
