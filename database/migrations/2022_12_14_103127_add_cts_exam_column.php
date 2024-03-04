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
            $table->string('cts_theory_exam_level')->nullable();
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
            $table->dropColumn('cts_theory_exam_level');
        });
    }
};
