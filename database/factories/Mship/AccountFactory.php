<?php

namespace Database\Factories\Mship;

use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => rand(10000000, 99999999),
            'name_first' => fake()->firstName,
            'name_last' => fake()->lastName,
            'email' => fake()->email,
            'is_invisible' => 0,
        ];
    }

    public function withQualification(): Factory
    {
        return $this->state(function () {
            $id = rand(10000000, 99999999);
            $qualAtc = Qualification::factory()->atc()->create();
            // Assoc qualification to account
            DB::table('mship_account_qualification')->insert([
                'account_id' => $id,
                'qualification_id' => $qualAtc->id,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);

            $qualPilot = Qualification::factory()->pilot()->create();
            // Assoc qualification to account
            DB::table('mship_account_qualification')->insert([
                'account_id' => $id,
                'qualification_id' => $qualPilot->id,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);

            return [];
        });
    }
}
