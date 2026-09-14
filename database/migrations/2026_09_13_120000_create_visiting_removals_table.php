<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visiting_removals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->index();
            $table->string('reason');
            $table->dateTime('removed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visiting_removals');
    }
};
