<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_waiting_list_theory_reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('waiting_list_account_id');
            $table->unsignedInteger('account_id');
            $table->unsignedBigInteger('seminar_id')->nullable();
            $table->string('status')->default('pending');
            $table->dateTime('reminded_at');
            $table->dateTime('last_attempt_at')->nullable();
            $table->dateTime('next_check_at');
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();

            // Shortened names because the default generated names exceed MySQL's 64 character identifier limit.
            $table->unique('waiting_list_account_id', 'wl_theory_reminders_account_unique');
            $table->index('next_check_at', 'wl_theory_reminders_next_check_index');

            $table->foreign('waiting_list_account_id', 'wl_theory_reminders_wla_foreign')
                ->references('id')
                ->on('training_waiting_list_account')
                ->cascadeOnDelete();
            $table->foreign('seminar_id', 'wl_theory_reminders_seminar_foreign')
                ->references('id')
                ->on('training_seminars')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_waiting_list_theory_reminders');
    }
};
