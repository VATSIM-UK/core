<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_seminars', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('waiting_list_id');
            $table->string('name');
            $table->string('description', 255)->nullable();
            $table->date('date');
            $table->time('from');
            $table->time('to');
            $table->unsignedSmallInteger('capacity');
            $table->unsignedSmallInteger('invitation_expiry_days')->default(7);
            $table->boolean('automatic_invitations_enabled')->default(false);
            $table->dateTime('closed_at')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedMediumInteger('cts_group_session_id')->nullable()->unique();
            $table->timestamps();

            $table->foreign('waiting_list_id')
                ->references('id')
                ->on('training_waiting_list')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_seminars');
    }
};
