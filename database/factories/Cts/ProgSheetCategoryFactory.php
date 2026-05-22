<?php

namespace Database\Factories\Cts;

use App\Models\Cts\ProgSheet;
use App\Models\Cts\ProgSheetCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<ProgSheetCategory>
 */
class ProgSheetCategoryFactory extends Factory
{
    protected $model = ProgSheetCategory::class;

    public function definition(): array
    {
        return [
            'prog_sheet_id' => ProgSheet::factory(),
            'catName' => $this->faker->words(2, true),
            'disabled' => 0,
        ];
    }

    public function forProgSheet(int $progSheetId): static
    {
        return $this->state(fn (array $attributes) => [
            'prog_sheet_id' => $progSheetId,
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'disabled' => 1,
        ]);
    }
}
