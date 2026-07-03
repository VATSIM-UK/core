<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_seminar_attendees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seminar_id');
            $table->unsignedInteger('account_id');
            $table->unsignedBigInteger('invitation_id')->nullable();
            $table->unsignedInteger('added_by')->nullable();
            $table->dateTime('added_at')->nullable();
            $table->unsignedInteger('cts_group_sessions_student_id')->nullable()->unique();
            $table->timestamps();

            $table->foreign('seminar_id')
                ->references('id')
                ->on('training_seminars')
                ->cascadeOnDelete();
            $table->foreign('invitation_id')
                ->references('id')
                ->on('training_seminar_invitations')
                ->nullOnDelete();
            $table->unique(['seminar_id', 'account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_seminar_attendees');
    }
};
