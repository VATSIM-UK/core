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
            $table->enum('top_ten_notified', ['yes', 'no'])->after('notes')->defalt('no');
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
            $table->dropColumn('top_ten_notified');
        });
    }
};
