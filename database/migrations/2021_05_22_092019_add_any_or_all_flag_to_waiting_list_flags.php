<?php

use App\Models\Training\WaitingList;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAnyOrAllFlagToWaitingListFlags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('training_waiting_list', function (Blueprint $table) {
            $table->string('flags_check')->default(WaitingList::ALL_FLAGS);
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
            $table->dropColumn('flags_check');
        });
    }
}
