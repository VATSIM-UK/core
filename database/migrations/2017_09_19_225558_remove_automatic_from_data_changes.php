<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveAutomaticFromDataChanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sys_data_change', function (Blueprint $table) {
            $table->dropColumn('automatic');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sys_data_change', function (Blueprint $table) {
            $table->boolean('automatic')->default(0)->after('data_new');
        });
    }
}
