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
        Schema::table('networkdata_atc', function (Blueprint $table) {
            // index to allow query pattern which retrieves an accounts ATC sessions by their disconnected timestamp
            // and then callsign.
            $table->index(['account_id', 'disconnected_at', 'callsign']);

            // index to allow query pattern which retrieves an accounts ATC sessions first by the callsign
            // and then by the disconnected timestamp
            $table->index(['account_id', 'callsign', 'disconnected_at']);

            // index to allow endorsement conditions to retrieve network data by account, callsign, connected_at,
            // qualification_id and deleted_at which helps boost performance
            // when a condition has a requirement for ATC sessions to be completed with a particular qualification
            // and within a particular number of months.
            // Deleted_at is included as the Networkdata/Atc model has soft deletes.
            $table->index(['account_id', 'callsign', 'connected_at', 'qualification_id', 'deleted_at']);
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
            $table->dropIndex(['account_id', 'disconnected_at', 'callsign']);
            $table->dropIndex(['account_id', 'callsign', 'disconnected_at']);
            $table->dropIndex(['account_id', 'callsign', 'connected_at', 'qualification_id', 'deleted_at']);
        });
    }
};
