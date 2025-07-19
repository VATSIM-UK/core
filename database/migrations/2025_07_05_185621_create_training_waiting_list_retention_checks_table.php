<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_waiting_list_retention_checks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('waiting_list_account_id');
            $table->string('token');
            $table->dateTime('expires_at');
            $table->dateTime('response_at')->nullable();
            $table->string('status');
            $table->dateTime('email_sent_at');
            $table->dateTime('removal_actioned_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_waiting_list_retention_checks');
    }
};
