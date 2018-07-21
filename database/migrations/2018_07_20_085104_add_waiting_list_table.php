<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWaitingListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('training_waiting_list', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->boolean('active')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('training_waiting_list_account', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('list_id');
            $table->unsignedInteger('account_id');
            $table->unsignedInteger('status_id');
            $table->integer('position')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('training_waiting_list_staff', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('list_id');
            $table->unsignedInteger('account_id');
            $table->timestamps();
        });

        Schema::create('training_waiting_list_status', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->boolean('retains_position')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('training_waiting_list');
        Schema::dropIfExists('training_waiting_list_account');
        Schema::dropIfExists('training_waiting_list_staff');
        Schema::dropIfExists('training_waiting_list_status');
    }
}
