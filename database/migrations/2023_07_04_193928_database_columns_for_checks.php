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
        Schema::table('training_waiting_list_account', function (Blueprint $table) {
            $table->json('flags_status_summary')->nullable();
            $table->boolean('eligible')->default(false);
            $table->json('eligibility_summary')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('training_waiting_list_account', function (Blueprint $table) {
            $table->dropColumn('flags_status_summary');
            $table->dropColumn('eligible');
            $table->dropColumn('eligibility_summary');
        });
    }
};
