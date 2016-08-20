<?php

namespace App\Modules\NetworkData\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class NetworkDataDatabaseSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		// $this->call('App\Modules\NetworkData\Database\Seeds\FoobarTableSeeder');
	}
}
