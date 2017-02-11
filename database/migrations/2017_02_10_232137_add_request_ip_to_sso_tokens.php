<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRequestIpToSsoTokens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('sso_token', function (Blueprint $table) {
          $table->bigInteger('request_ip')->nullable()->after('account_id');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('sso_token', function (Blueprint $table) {
          $table->dropColumn('request_ip');
      });
    }
}
