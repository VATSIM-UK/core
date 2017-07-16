<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveApiAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('api_account');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('api_account', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 60);
            $table->string('api_token', 60)->unique();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
