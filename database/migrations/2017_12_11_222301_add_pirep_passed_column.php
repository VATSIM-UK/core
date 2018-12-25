<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPirepPassedColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('smartcars_pirep', function (Blueprint $table) {
            $table->boolean('passed')->after('status')->nullable();
            $table->string('pass_reason')->after('passed')->nullable();
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
            $table->dropColumn('passed');
            $table->dropColumn('pass_reason');
        });
    }
}
