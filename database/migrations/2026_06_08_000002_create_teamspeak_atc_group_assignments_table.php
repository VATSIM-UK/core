<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teamspeak_atc_group_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('account_id');
            $table->foreignId('atc_server_group_id')
                ->constrained('teamspeak_atc_server_groups')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique('account_id');
            $table->foreign('account_id')->references('id')->on('mship_account');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teamspeak_atc_group_assignments');
    }
};
