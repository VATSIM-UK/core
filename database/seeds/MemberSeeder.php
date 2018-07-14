<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        factory(\App\Models\Mship\Account::class, 50)->create();

    }
}
