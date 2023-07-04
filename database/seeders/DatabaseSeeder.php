<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Caution: This seeder is run on production too!
        $this->call(RolesAndPermissionsSeeder::class);
    }
}
