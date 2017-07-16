<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOauthClientsToEmails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('sso_email', 'mship_oauth_emails');
        Schema::table('mship_oauth_emails', function (Blueprint $table) {
            $table->dropColumn('account_id');
        });

        Schema::drop('sso_account');
        Schema::drop('sso_token');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('sso_account', function ($table) {
            $table->increments('id')->unsigned();
            $table->string('username', 15);
            $table->string('name', 15);
            $table->string('api_key_public', 50);
            $table->string('api_key_private', 50);
            $table->string('salt', 25);
            $table->timestamps();
        });

        Schema::create('sso_token', function ($table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('token', 120);
            $table->integer('sso_account_id');
            $table->text('return_url');
            $table->integer('account_id');
            $table->string('request_ip', 45)->default('0.0.0.0');
            $table->timestamps();
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
        });

        Schema::table('mship_oauth_emails', function (Blueprint $table) {
            $table->unsignedInteger('account_id');
        });

        Schema::rename('mship_oauth_emails', 'sso_email');
    }
}
