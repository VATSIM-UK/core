<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AllowNullSmartcarsColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('smartcars_pirep', function (Blueprint $table) {
            $table->mediumText('comments')->nullable()->change();
        });

        Schema::table('smartcars_posrep', function (Blueprint $table) {
            $table->time('time_departure')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('smartcars_pirep', function (Blueprint $table) {
            $table->mediumText('comments')->nullable(false)->change();
        });

        Schema::table('smartcars_posrep', function (Blueprint $table) {
            $table->time('time_departure')->nullable(false)->change();
        });
    }
}
