<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class VtFacilitiesPublicColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vt_facility', function (Blueprint $table) {
            $table->boolean('public')->default(true)->after('open');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vt_facility', function (Blueprint $table) {
            $table->dropColumn('public');
        });
    }
}
