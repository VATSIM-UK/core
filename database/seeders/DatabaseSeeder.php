<?php

namespace Database\Seeders;

use Database\Seeders\Testing\CtsExamSeeder;
use Database\Seeders\Testing\PositionsAndEndorsementsSeeder;
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
        // $this->call(RolesAndPermissionsSeeder::class);
        $this->call(CtsExamSeeder::class);
        $this->call(PositionsAndEndorsementsSeeder::class);
    }
}
