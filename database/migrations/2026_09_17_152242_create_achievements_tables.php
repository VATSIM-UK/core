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
        Schema::create('mship_achievement', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('mship_account')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('mship_account')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('mship_account')->nullOnDelete();
        });

        Schema::create('mship_achievement_award', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('achievement_id');
            $table->unsignedInteger('account_id');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('achievement_id')->references('id')->on('mship_achievement')->cascadeOnDelete();
            $table->foreign('account_id')->references('id')->on('mship_account')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('mship_account')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('mship_account')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('mship_account')->nullOnDelete();

            $table->unique(['achievement_id', 'account_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mship_achievement_award');
        Schema::dropIfExists('mship_achievement');
    }
};
