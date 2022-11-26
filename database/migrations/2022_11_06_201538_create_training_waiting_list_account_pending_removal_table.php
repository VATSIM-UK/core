<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('training_waiting_list_account_pending_removal', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('waiting_list_account_id');
            $table->enum('status', ['Pending', 'Completed', 'Cancelled'])->default('Pending');
            $table->unsignedInteger('emails_sent')->default(0);
            $table->timestamp('removal_date');
            $table->timestamps();

            $table->foreign('waiting_list_account_id', 'waiting_list_account_pending_removal_id')->references('id')->on('training_waiting_list_account');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('training_waiting_list_account_pending_removal');
    }
};
