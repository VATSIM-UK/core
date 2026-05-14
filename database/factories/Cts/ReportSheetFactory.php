<?php

namespace Database\Factories\Cts;

use App\Models\Cts\Member;
use App\Models\Cts\ProgSheet;
use App\Models\Cts\ProgSheetField;
use App\Models\Cts\ReportSheet;
use App\Models\Cts\Session;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<ReportSheet>
 */
class ReportSheetFactory extends Factory
{
    protected $model = ReportSheet::class;

    public function definition(): array
    {
        $progSheet = ProgSheet::factory()->create();
        $field = ProgSheetField::factory()->forProgSheet($progSheet->prog_sheet_id)->create();

        return [
            'seshid' => Session::factory(),
            'student_id' => Member::factory()->create()->id,
            'prog_sheet_id' => $progSheet->prog_sheet_id,
            'field_id' => $field->field_id,
            'notes' => $this->faker->paragraph,
            'field_score' => $this->faker->numberBetween(0, 4),
        ];
    }

    public function forSession(int $sessionId): static
    {
        return $this->state(fn (array $attributes) => [
            'seshid' => $sessionId,
        ]);
    }

    public function forStudent(int $studentId): static
    {
        return $this->state(fn (array $attributes) => [
            'student_id' => $studentId,
        ]);
    }

    public function forField(int $fieldId): static
    {
        return $this->state(fn (array $attributes) => [
            'field_id' => $fieldId,
        ]);
    }
}
