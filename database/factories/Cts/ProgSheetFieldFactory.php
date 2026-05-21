<?php

namespace Database\Factories\Cts;

use App\Models\Cts\ProgSheet;
use App\Models\Cts\ProgSheetCategory;
use App\Models\Cts\ProgSheetField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<ProgSheetField>
 */
class ProgSheetFieldFactory extends Factory
{
    protected $model = ProgSheetField::class;

    public function definition(): array
    {
        $progSheet = ProgSheet::factory()->create();
        $category = ProgSheetCategory::factory()->forProgSheet($progSheet->prog_sheet_id)->create();

        return [
            'prog_sheet_id' => $progSheet->prog_sheet_id,
            'catId' => $category->catId,
            'groupId' => $this->faker->numberBetween(1, 10),
            'field' => $this->faker->sentence,
            'created_by' => 0,
            'created_date' => now(),
            'disabled' => 0,
        ];
    }

    public function forProgSheet(int $progSheetId): static
    {
        return $this->state(fn (array $attributes) => [
            'prog_sheet_id' => $progSheetId,
        ]);
    }

    public function forCategory(int $catId): static
    {
        return $this->state(fn (array $attributes) => [
            'catId' => $catId,
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'disabled' => 1,
        ]);
    }
}
