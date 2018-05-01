<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFailedPosrepColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('smartcars_pirep', function (Blueprint $table) {
            $table->unsignedInteger('failed_at')->after('pass_reason')->nullable();
            $table->foreign('failed_at')->references('id')->on('smartcars_posrep');
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
            $table->dropForeign('smartcars_pirep_failed_at_foreign');
            $table->dropColumn('failed_at');
        });
    }
}
