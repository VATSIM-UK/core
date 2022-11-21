<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('training_waiting_list', function (Blueprint $table) {
            $table->enum('enforce_hour_requirement', ['yes', 'no'])->after('flags_check')->defalt('yes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('training_waiting_list', function (Blueprint $table) {
            $table->dropColumn('enforce_hour_requirement');
        });
    }
};