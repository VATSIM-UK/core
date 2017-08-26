<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSmartcarsFlightMetadata extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('smartcars_flight', function (Blueprint $table) {
            $table->string('name')->after('code');
            $table->text('description')->after('name');
            $table->boolean('featured')->after('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('smartcars_flight', function (Blueprint $table) {
            $table->dropColumn('name', 'description', 'featured');
        });
    }
}
