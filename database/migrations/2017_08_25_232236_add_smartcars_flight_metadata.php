<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSmartcarsFlightMetadata extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE smartcars_airport MODIFY latitude DOUBLE(12,8) NOT NULL');
        DB::statement('ALTER TABLE smartcars_airport MODIFY longitude DOUBLE(12,8) NOT NULL');

        Schema::table('smartcars_flight', function (Blueprint $table) {
            $table->string('name')->after('code');
            $table->text('description')->after('name');
            $table->boolean('featured')->default(0)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE smartcars_airport MODIFY latitude DOUBLE(8,2)');
        DB::statement('ALTER TABLE smartcars_airport MODIFY longitude DOUBLE(8,2)');

        Schema::table('smartcars_flight', function (Blueprint $table) {
            $table->dropColumn('name', 'description', 'featured');
        });
    }
}
