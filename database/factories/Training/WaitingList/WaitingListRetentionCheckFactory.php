<?php

namespace Database\Factories\Training\WaitingList;

use App\Models\Training\WaitingList\WaitingListRetentionCheck;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class WaitingListRetentionCheckFactory extends Factory
{
    protected $model = WaitingListRetentionCheck::class;

    public function definition(): array
    {
        return [
            'waiting_list_account_id' => $this->faker->randomNumber(),
            'token' => Str::random(10),
            'expires_at' => Carbon::now(),
            'response_at' => Carbon::now(),
            'status' => $this->faker->word(),
            'email_sent_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
