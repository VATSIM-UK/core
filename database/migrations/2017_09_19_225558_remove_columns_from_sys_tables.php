<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveColumnsFromSysTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sys_activity', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });

        Schema::table('sys_data_change', function (Blueprint $table) {
            $table->dropColumn('automatic');
            $table->dropColumn('updated_at');
        });

        Schema::table('sys_notification_read', function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sys_activity', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });

        Schema::table('sys_data_change', function (Blueprint $table) {
            $table->boolean('automatic')->default(0)->after('data_new');
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });

        Schema::table('sys_notification_read', function (Blueprint $table) {
            $table->timestamps();
        });
    }
}
