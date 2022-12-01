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
            $table->timestamp('within_top_ten_notification_sent_at')->nullable()->after('notes');
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
            $table->dropColumn('within_top_ten_notification_sent_at');
        });
    }
};
