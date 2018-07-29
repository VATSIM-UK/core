<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('training_waiting_list_account', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('list_id');
            $table->unsignedInteger('account_id');
            $table->unsignedInteger('added_by')->nullable();
            $table->integer('position')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('training_waiting_list_account_status', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('waiting_list_account_id');
            $table->unsignedInteger('status_id');
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
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
            $table->boolean('default')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('training_waiting_list_status')->insert([
            'name' => 'Active',
            'retains_position' => true,
            'default' => true,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
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
        Schema::dropIfExists('training_waiting_list_account_status');
        Schema::dropIfExists('training_waiting_list_staff');
        Schema::dropIfExists('training_waiting_list_status');
    }
}
