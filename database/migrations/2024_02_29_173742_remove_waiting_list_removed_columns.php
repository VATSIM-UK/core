<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::drop('training_waiting_list_status');
        Schema::drop('training_waiting_list_account_status');
        Schema::table('training_waiting_list', function (Blueprint $table) {
            $table->dropColumn('flags_check');
        });

        Schema::table('training_waiting_list_account', function (Blueprint $table) {
            $table->dropColumn('eligible');
            $table->dropColumn('eligibility_summary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
