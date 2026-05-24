<?php

declare(strict_types=1);

namespace App\Repositories\Cts;

use App\Enums\FieldScore;
use App\Models\Cts\ProgSheetField;
use App\Models\Cts\ReportSheet;
use App\Models\Cts\Session;
use App\Models\Training\TrainingPosition\TrainingPosition;
use Illuminate\Support\Collection;

class MentoringReportRepository
{
    public function upsertCriterion(
        Session $session,
        int $fieldId,
        FieldScore $score,
        ?string $notes = null,
    ): void {
        ReportSheet::updateOrCreate(
            [
                'seshid' => $session->id,
                'field_id' => $fieldId,
            ],
            [
                'student_id' => $session->student_id,
                'prog_sheet_id' => $session->progress_sheet_id,
                'field_score' => $score->value,
                'notes' => $notes ?? '',
            ],
        );
    }

    public function upsertAdditionalComments(Session $session, ?string $notes): void
    {
        $this->upsertCriterion($session, 0, FieldScore::NOT_SCORED, $notes);
    }

    /**
     * @return Collection<int, ProgSheetField>
     */
    public function getCriteriaFieldsForSession(Session $session): Collection
    {
        return ProgSheetField::query()
            ->where('prog_sheet_id', $session->progress_sheet_id)
            ->where('disabled', '!=', 1)
            ->with('category')
            ->orderBy('field_id')
            ->get();
    }

    /**
     * @return array<int, FieldScore>
     */
    public function getExistingScoresForSession(Session $session): array
    {
        return ReportSheet::query()
            ->where('seshid', $session->id)
            ->where('field_id', '!=', 0)
            ->get()
            ->mapWithKeys(fn (ReportSheet $sheet) => [$sheet->field_id => $sheet->field_score])
            ->all();
    }

    public function getExistingAdditionalComments(Session $session): ?string
    {
        $notes = ReportSheet::query()
            ->where('seshid', $session->id)
            ->where('field_id', 0)
            ->value('notes');

        return $notes !== null && $notes !== '' ? $notes : null;
    }

    /**
     * @return array<int, FieldScore>
     */
    public function getPreviousScoresForStudent(Session $session): array
    {
        $previousSession = $this->getPreviousFiledSession($session);

        if (! $previousSession) {
            return [];
        }

        return ReportSheet::query()
            ->where('seshid', $previousSession->id)
            ->where('field_id', '!=', 0)
            ->get()
            ->mapWithKeys(fn (ReportSheet $sheet) => [$sheet->field_id => $sheet->field_score])
            ->all();
    }

    /**
     * @return array<int, FieldScore>
     */
    public function getBestScoresForStudent(Session $session): array
    {
        $relatedPositions = $this->getRelatedPositions($session);

        $sessionIds = Session::query()
            ->where('student_id', $session->student_id)
            ->whereIn('position', $relatedPositions)
            ->where('progress_sheet_id', $session->progress_sheet_id)
            ->whereNotNull('filed')
            ->whereNull('cancelled_datetime')
            ->where('noShow', 0)
            ->where('id', '!=', $session->id)
            ->pluck('id');

        if ($sessionIds->isEmpty()) {
            return [];
        }

        $bestScores = [];

        ReportSheet::query()
            ->whereIn('seshid', $sessionIds)
            ->where('field_id', '!=', 0)
            ->get()
            ->each(function (ReportSheet $sheet) use (&$bestScores): void {
                $fieldId = $sheet->field_id;
                $score = $sheet->field_score;

                if (! isset($bestScores[$fieldId]) || $score->value > $bestScores[$fieldId]->value) {
                    $bestScores[$fieldId] = $score;
                }
            });

        return $bestScores;
    }

    public function getPreviousFiledSession(Session $session): ?Session
    {
        $relatedPositions = $this->getRelatedPositions($session);

        return Session::query()
            ->where('student_id', $session->student_id)
            ->whereIn('position', $relatedPositions)
            ->where('progress_sheet_id', $session->progress_sheet_id)
            ->whereNotNull('filed')
            ->whereNull('cancelled_datetime')
            ->where('noShow', 0)
            ->where('id', '!=', $session->id)
            ->where(function ($query) use ($session): void {
                $query->where('taken_date', '<', $session->taken_date)
                    ->orWhere(function ($inner) use ($session) {
                        $inner->where('taken_date', $session->taken_date)
                            ->where('taken_from', '<', $session->taken_from);
                    });
            })
            ->orderByDesc('taken_date')
            ->orderByDesc('taken_from')
            ->first();
    }

    public function getPrimaryPosition(string $position): string
    {
        $callsigns = TrainingPosition::query()
            ->whereJsonContains('cts_positions', $position)
            ->get()
            ->flatMap(fn (TrainingPosition $trainingPosition) => $trainingPosition->cts_positions ?? [])
            ->unique()
            ->filter()
            ->values()
            ->all();

        return $callsigns[0] ?? $position;
    }

    /**
     * @return array<int, string>
     */
    public function getRelatedPositions(Session $session): array
    {
        $callsigns = TrainingPosition::query()
            ->whereJsonContains('cts_positions', $session->position)
            ->get()
            ->flatMap(fn (TrainingPosition $position) => $position->cts_positions ?? [])
            ->unique()
            ->filter()
            ->values()
            ->all();

        if ($callsigns === []) {
            return [$session->position];
        }

        return $callsigns;
    }

    public function fileSession(Session $session, bool $noShow = false): void
    {
        $session->update([
            'session_done' => 1,
            'book_done' => 1,
            'filed' => now(),
            'noShow' => $noShow ? 1 : 0,
        ]);
    }
}
