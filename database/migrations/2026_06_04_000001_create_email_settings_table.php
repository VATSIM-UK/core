<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mship_email_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('account_id');
            $table->string('email_type', 100);
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['account_id', 'email_type']);
            $table->foreign('account_id')->references('id')->on('mship_account')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mship_email_settings');
    }
};
