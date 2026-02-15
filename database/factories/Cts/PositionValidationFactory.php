<?php

namespace Database\Factories\Cts;

use App\Enums\PositionValidationStatusEnum;
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
            'status' => fake()->randomElement(PositionValidationStatusEnum::cases()),
            'changed_by' => 1111111,
            'date_changed' => Carbon::createFromFormat('Y-m-d H:i:s', now())->toDateTimeString(),
        ];
    }

    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PositionValidationStatusEnum::Student,
        ]);
    }

    public function mentor(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PositionValidationStatusEnum::Mentor,
        ]);
    }
}
