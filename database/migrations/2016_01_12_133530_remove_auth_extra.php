<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveAuthExtra extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mship_account', function ($table) {
            $table->dropColumn('auth_extra');
            $table->dropColumn('auth_extra_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mship_account', function ($table) {
            $table->boolean('auth_extra')->default(0);
            $table->timestamp('auth_extra_at')->nullable();
        });
    }
}
