<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;

class AddSmartcarsPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mship_permission')->insert([
            ['name' => 'smartcars', 'display_name' => 'smartCARS', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'smartcars/aircraft', 'display_name' => 'smartCARS / Aircraft', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'smartcars/aircraft/create', 'display_name' => 'smartCARS / Aircraft / Create', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'smartcars/aircraft/update', 'display_name' => 'smartCARS / Aircraft / Update', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'smartcars/aircraft/delete', 'display_name' => 'smartCARS / Aircraft / Delete', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'smartcars/airports', 'display_name' => 'smartCARS / Airports', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'smartcars/airports/create', 'display_name' => 'smartCARS / Airport / Create', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'smartcars/airports/update', 'display_name' => 'smartCARS / Airport / Update', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'smartcars/airports/delete', 'display_name' => 'smartCARS / Airport / Delete', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'smartcars/exercises', 'display_name' => 'smartCARS / Exercises', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'smartcars/exercises/create', 'display_name' => 'smartCARS / Exercise / Create', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'smartcars/exercises/update', 'display_name' => 'smartCARS / Exercise / Update', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'smartcars/exercises/delete', 'display_name' => 'smartCARS / Exercise / Delete', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'smartcars/flights', 'display_name' => 'smartCARS / Flights', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'smartcars/flights/override', 'display_name' => 'smartCARS / Flight / Approve or Deny', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('mship_permission')->whereIn('name', [
            'smartcars',
            'smartcars/aircraft',
            'smartcars/aircraft/create',
            'smartcars/aircraft/update',
            'smartcars/aircraft/delete',
            'smartcars/airports',
            'smartcars/airport/create',
            'smartcars/airport/update',
            'smartcars/airport/delete',
            'smartcars/exercises',
            'smartcars/exercise/create',
            'smartcars/exercise/update',
            'smartcars/exercise/delete',
            'smartcars/flights',
            'smartcars/flight/override',
        ])->delete();
    }
}
