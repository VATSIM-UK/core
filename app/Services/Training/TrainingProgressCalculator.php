<?php

namespace App\Services\Training;

use App\Enums\FieldScore;
use App\Models\Cts\ProgSheetCategory;
use App\Models\Cts\ProgSheetField;
use App\Models\Cts\ReportSheet;
use App\Models\Cts\Session;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Illuminate\Support\Collection;

/**
 * Calculates training progress for a student's TrainingPlace.
 *
 * Returns a shape of:
 *   [
 *     'percentage'    => int,
 *     'sessionIds'    => int[],
 *     'latestSessionId' => int|null,
 *     'categories'    => [
 *       ['name' => string, 'percentage' => int, 'fields' => [...]],
 *       ...
 *     ],
 *   ]
 */
class TrainingProgressCalculator
{
    private ?array $result = null;

    public function __construct(private readonly TrainingPlace $trainingPlace) {}

    public function calculate(): array
    {
        if ($this->result !== null) {
            return $this->result;
        }

        $sessionIds = $this->resolveSessionIds();

        if (empty($sessionIds)) {
            return $this->result = $this->empty();
        }

        $bestScores = $this->resolveBestScores($sessionIds);
        $categories = $this->resolveCategories($sessionIds, array_keys($bestScores));

        return $this->result = $this->buildResult($sessionIds, $categories, $bestScores);
    }

    private function resolveSessionIds(): array
    {
        $studentId = $this->trainingPlace->account->member?->id;
        $positions = $this->trainingPlace->trainingPosition?->cts_positions ?? [];

        if (! $studentId || empty($positions)) {
            return [];
        }

        $upperBound = $this->trainingPlace->deleted_at?->copy() ?? now();

        return Session::query()
            ->where('student_id', $studentId)
            ->whereIn('position', $positions)
            ->where('taken', 1)
            ->whereNotNull('filed')
            ->whereDate('taken_date', '>=', $this->trainingPlace->created_at)
            ->whereDate('taken_date', '<=', $upperBound)
            ->orderBy('taken_date')
            ->pluck('id')
            ->all();
    }

    private function resolveBestScores(array $sessionIds): array
    {
        $bestScores = [];

        ReportSheet::query()
            ->whereIn('seshid', $sessionIds)
            ->where('field_id', '!=', 0)
            ->get()
            ->each(function (ReportSheet $sheet) use (&$bestScores): void {
                $fieldId = $sheet->field_id;
                $score = $sheet->field_score;

                if ($score->value === FieldScore::NOT_SCORED->value) {
                    return;
                }

                if (! isset($bestScores[$fieldId]) || $score->value > $bestScores[$fieldId]->value) {
                    $bestScores[$fieldId] = $score;
                }
            });

        return $bestScores;
    }

    private function resolveCategories(array $sessionIds, array $fieldIds): Collection
    {
        $progSheetId = Session::find(reset($sessionIds))?->progress_sheet_id;

        if ($progSheetId) {
            return $this->categoriesFromProgSheet($progSheetId);
        }

        if (! empty($fieldIds)) {
            return $this->categoriesFromFieldIds($fieldIds);
        }

        return collect();
    }

    private function categoriesFromProgSheet(int $progSheetId): Collection
    {
        return ProgSheetCategory::query()
            ->where('prog_sheet_id', $progSheetId)
            ->with(['fields' => fn ($q) => $q->where('disabled', '!=', 1)])
            ->get();
    }

    private function categoriesFromFieldIds(array $fieldIds): Collection
    {
        $grouped = ProgSheetField::query()
            ->whereIn('field_id', $fieldIds)
            ->where('disabled', '!=', 1)
            ->with('category')
            ->get()
            ->groupBy(fn ($f) => $f->category?->catName ?? 'Uncategorised');

        return $grouped->map(fn ($fields, $catName) => new class($catName, $fields)
        {
            public function __construct(public readonly string $catName, public readonly Collection $fields) {}
        });
    }

    private function buildResult(array $sessionIds, Collection $categories, array $bestScores): array
    {
        $categoryData = [];
        $totalAssessedScore = 0;
        $totalCriteriaCount = 0;

        foreach ($categories as $category) {
            ['data' => $catData, 'assessedScore' => $catAssessedScore, 'total' => $catTotal]
                = $this->buildCategoryData($category, $bestScores);

            $categoryData[] = $catData;
            $totalAssessedScore += $catAssessedScore;
            $totalCriteriaCount += $catTotal;
        }

        $percentage = $totalCriteriaCount > 0
            ? min(100, round(($totalAssessedScore / ($totalCriteriaCount * 100)) * 100))
            : 0;

        return [
            'percentage' => $percentage,
            'sessionIds' => $sessionIds,
            'latestSessionId' => empty($sessionIds) ? null : end($sessionIds),
            'categories' => $categoryData,
        ];
    }

    private function buildCategoryData(object $category, array $bestScores): array
    {
        $fields = $category->fields ?? collect();
        $assessedScore = 0;
        $criteria = [];

        foreach ($fields as $field) {
            $score = $bestScores[$field->field_id] ?? null;
            $criteria[] = [
                'name' => $field->field ?? "Criteria #{$field->field_id}",
                'field_id' => $field->field_id,
                'best_score' => $score,
                'best_score_label' => $score?->getLabel() ?? 'Not Assessed',
                'best_score_color' => $score?->getColor() ?? 'gray',
            ];

            if ($score) {
                $assessedScore += $score->toPercentage();
            }
        }

        $total = $fields->count();
        $percentage = $total > 0
            ? min(100, round(($assessedScore / ($total * 100)) * 100))
            : 0;

        return [
            'data' => [
                'name' => $category->catName ?? 'Uncategorised',
                'percentage' => $percentage,
                'fields' => $criteria,
            ],
            'assessedScore' => $assessedScore,
            'total' => $total,
        ];
    }

    private function empty(): array
    {
        return [
            'percentage' => 0,
            'sessionIds' => [],
            'latestSessionId' => null,
            'categories' => [],
        ];
    }
}
