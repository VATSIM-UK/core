<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexToNetworkDataTimestamps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('networkdata_atc', function (Blueprint $table) {
            $table->index('connected_at');
            $table->index('disconnected_at');
        });

        Schema::table('networkdata_pilots', function (Blueprint $table) {
            $table->index('connected_at');
            $table->index('disconnected_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('networkdata_atc', function (Blueprint $table) {
            $table->dropIndex('networkdata_atc_connected_at_index');
            $table->dropIndex('networkdata_atc_disconnected_at_index');
        });

        Schema::table('networkdata_pilots', function (Blueprint $table) {
            $table->dropIndex('networkdata_pilots_connected_at_index');
            $table->dropIndex('networkdata_pilots_disconnected_at_index');
        });
    }
}
