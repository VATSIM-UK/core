<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_seminar_invitations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seminar_id');
            $table->unsignedInteger('account_id');
            $table->unsignedInteger('waiting_list_account_id')->nullable();
            $table->string('token')->unique();
            $table->enum('status', [
                'sent',
                'attending',
                'not_interested',
                'cannot_attend',
                'expired',
                'removed_no_response',
                'removed_two_cannot_attend',
            ])->default('sent');
            $table->dateTime('sent_at');
            $table->dateTime('responded_at')->nullable();
            $table->dateTime('expires_at');
            $table->timestamps();

            $table->foreign('seminar_id')
                ->references('id')
                ->on('training_seminars')
                ->cascadeOnDelete();
            $table->unique(['seminar_id', 'account_id']);
            $table->index(['account_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_seminar_invitations');
    }
};
