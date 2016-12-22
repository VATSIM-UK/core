<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_request', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('api_account_id');
            $table->enum('method', ['POST', 'GET', 'PUT', 'PATCH', 'DELETE']);
            $table->string('url_name', 100);
            $table->text('url_full');
            $table->integer('response_code')->unsigned()->nullable();
            $table->text('response_full')->nullable();
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
        Schema::dropIfExists('api_request');
    }
}
