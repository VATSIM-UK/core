<?php

declare(strict_types=1);

namespace App\Filament\Training\Support;

use App\Enums\FieldScore;
use App\Models\Cts\ReportSheet;
use App\Models\Cts\Session;
use Illuminate\Support\Collection;

final class MentoringReportScores
{
    /**
     * @param  Collection<int, Session>  $sessions
     * @return array<int, array<int, FieldScore>> [field_id => [session_id => score]]
     */
    public static function scoreMapForSessions(Collection $sessions): array
    {
        $map = [];

        foreach ($sessions as $session) {
            foreach ($session->reportSheets as $sheet) {
                if (! $sheet instanceof ReportSheet) {
                    continue;
                }

                $map[$sheet->field_id][$session->id] = $sheet->field_score;
            }
        }

        return $map;
    }

    /**
     * @param  array<int, array<int, FieldScore>>  $scoreMap
     */
    public static function bestScore(array $scoreMap, int $fieldId): FieldScore
    {
        $fieldScores = collect($scoreMap[$fieldId] ?? []);

        if ($fieldScores->isEmpty()) {
            return FieldScore::NOT_SCORED;
        }

        return $fieldScores->sortByDesc(fn (FieldScore $s) => $s->value)->first() ?? FieldScore::NOT_SCORED;
    }

    /**
     * @param  array<int, array<int, FieldScore>>  $scoreMap
     */
    public static function previousScore(array $scoreMap, int $fieldId, ?Session $previousSession): FieldScore
    {
        if (! $previousSession) {
            return FieldScore::NOT_SCORED;
        }

        return $scoreMap[$fieldId][$previousSession->id] ?? FieldScore::NOT_SCORED;
    }
}
