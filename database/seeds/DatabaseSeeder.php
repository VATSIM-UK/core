<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        \App\Models\Mship\Account::create([
            'id' => 980234,
            'name_first' => 'Anthony',
            'name_last' => 'Lawrence',
            'joined_at' => \Carbon\Carbon::now(),
        ]);

        \App\Models\Mship\Account\Email::create([
            'id' => 980234,
            'email' => 'anthony.lawrence@vatsim-uk.co.uk',
            'is_primary' => true,
            'verified_at' => \Carbon\Carbon::now(),
        ]);

        \App\Models\Mship\Account\Qualification::create([
            'id' => 980234,
            'qualification_id' => \App\Models\Mship\Qualification::parseVatsimATCQualification(1)->qualification_id,
            'created_at' => \Carbon\Carbon::parse('4 years ago'),
        ]);

        \App\Models\Mship\Account\Qualification::create([
            'id' => 980234,
            'qualification_id' => \App\Models\Mship\Qualification::parseVatsimATCQualification(2)->qualification_id,
            'created_at' => \Carbon\Carbon::parse('3 years ago'),
        ]);

        \App\Models\Mship\Account\Qualification::create([
            'id' => 980234,
            'qualification_id' => \App\Models\Mship\Qualification::parseVatsimATCQualification(3)->qualification_id,
            'created_at' => \Carbon\Carbon::parse('3 years ago'),
        ]);

        \App\Models\Mship\Account\Qualification::create([
            'id' => 980234,
            'qualification_id' => \App\Models\Mship\Qualification::parseVatsimATCQualification(4)->qualification_id,
            'created_at' => \Carbon\Carbon::parse('2 years ago'),
        ]);

        \App\Models\Mship\Account\Qualification::create([
            'id' => 980234,
            'qualification_id' => \App\Models\Mship\Qualification::parseVatsimATCQualification(5)->qualification_id,
            'created_at' => \Carbon\Carbon::parse('1 year ago'),
        ]);

        DB::table('mship_account_role')->insert([
            'id' => 980234,
            'role_id' => 1,
        ]);

        DB::table('mship_account_state')->insert([
            'id' => 980234,
            'state' => 60,
        ]);

        Model::reguard();
    }
}
