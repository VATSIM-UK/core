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
        Schema::create('training_place_offers', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('waiting_list_account_id');
            $table->unsignedBigInteger('training_position_id');
            $table->string('token');
            $table->timestamp('expires_at');
            $table->enum('status', ['pending', 'accepted', 'declined']);
            $table->dateTime('response_at')->nullable();
            $table->text('decline_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_place_offers');
    }
};
