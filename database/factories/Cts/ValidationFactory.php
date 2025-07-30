<?php

namespace Database\Factories\Cts;

use App\Models\Cts\Member;
use App\Models\Cts\Validation;
use App\Models\Cts\ValidationPosition;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ValidationFactory extends Factory
{
    protected $model = Validation::class;

    public function definition(): array
    {
        return [
            'position_id' => ValidationPosition::Factory()->create()->id,
            'member_id' => Member::Factory()->create()->id,
            'awarded_by' => Member::Factory()->create()->id,
            'awarded_date' => Carbon::createFromFormat('Y-m-d H:i:s', now())->toDateTimeString(),
        ];
    }
}
