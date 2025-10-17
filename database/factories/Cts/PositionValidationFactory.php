<?php

namespace Database\Factories\Cts;

use App\Models\Cts\Member;
use App\Models\Cts\Position;
use App\Models\Cts\PositionValidation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class PositionValidationFactory extends Factory
{
    protected $model = PositionValidation::class;

    public function definition(): array
    {
        return [
            'member_id' => Member::Factory()->create()->id,
            'position_id' => Position::factory()->create()->id,
            'status' => rand(1, 5),
            'changed_by' => 1111111,
            'date_changed' => Carbon::createFromFormat('Y-m-d H:i:s', now())->toDateTimeString(),
        ];
    }
}
