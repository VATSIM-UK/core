<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBidTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('smartcars_bid', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('flight_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->timestamps();
            $table->timestamp('completed_at')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('smartcars_bid');
    }
}
