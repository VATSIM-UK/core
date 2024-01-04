<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('endorsements', 'position_groups');
        Schema::rename('endorsement_conditions', 'position_group_conditions');
        Schema::table('position_group_conditions', function (Blueprint $table) {
            $table->renameColumn('endorsement_id', 'position_group_id');
        });
        Schema::rename('endorsement_stations', 'position_group_positions');
        Schema::rename('stations', 'positions');
        Schema::rename('airport_stations', 'airport_positions');
        Schema::table('airport_positions', function (Blueprint $table) {
            $table->renameColumn('station_id', 'position_id');
        });
        Schema::table('training_waiting_list_flags', function (Blueprint $table) {
            $table->renameColumn('endorsement_id', 'position_group_id');
        });
    }
};
