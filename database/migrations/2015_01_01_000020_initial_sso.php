<?php

use Illuminate\Database\Migrations\Migration;

class InitialSso extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sso_account', function ($table) {
            $table->increments('sso_account_id')->unsigned();
            $table->string('username', 15);
            $table->string('name', 15);
            $table->string('api_key_public', 50);
            $table->string('api_key_private', 50);
            $table->string('salt', 25);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sso_email', function ($table) {
            $table->bigIncrements('sso_email_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->bigInteger('account_email_id')->unsigned()->nullable();
            $table->integer('sso_account_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sso_token', function ($table) {
            $table->bigIncrements('sso_token_id')->unsigned();
            $table->string('token', 120);
            $table->integer('sso_account_id');
            $table->text('return_url');
            $table->integer('account_id');
            $table->timestamps();
            $table->timestamp('expires_at')->nullable();
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
        Schema::dropIfExists('sso_account');
        Schema::dropIfExists('sso_email');
        Schema::dropIfExists('sso_token');
    }
}
