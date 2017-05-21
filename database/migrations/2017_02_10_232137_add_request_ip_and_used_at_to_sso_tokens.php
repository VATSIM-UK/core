<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRequestIpAndUsedAtToSsoTokens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sso_token', function (Blueprint $table) {
            $table->string('request_ip', 45)->after('account_id')->default('0.0.0.0');
            $table->timestamp('used_at')->after('created_at')->nullable();
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
            $table->dropColumn('used_at');
        });
    }
}
