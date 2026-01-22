<?php

namespace App\Filament\Training\Pages\Exam\Widgets;

use App\Models\Cts\PracticalResult;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ExamOverview extends BaseWidget
{
    protected ?string $heading = 'Exam Overview for Current Year';

    protected function getCards(): array
    {
        $startofYear = now()->startOfYear();
        $endofYear = now()->endOfYear();

        $overall = PracticalResult::select(
            DB::raw('count(*) as total'),
            DB::raw("SUM(CASE WHEN result = '".PracticalResult::PASSED."' THEN 1 ELSE 0 END) as passed"),
            DB::raw("SUM(CASE WHEN result = '".PracticalResult::FAILED."' THEN 1 ELSE 0 END) as failed"),
            DB::raw("SUM(CASE WHEN result = '".PracticalResult::INCOMPLETE."' THEN 1 ELSE 0 END) as incomplete"),
        )
            ->WhereBetween('date', [$startofYear, $endofYear])
            ->first();

        $examStats = PracticalResult::whereBetween('date', [$startofYear, $endofYear])
            ->select('exam', 'result', DB::raw('count(*) as total'))
            ->groupBy('exam', 'result')
            ->get()
            ->groupBy('exam');

        $cards = [
            Stat::make('Total Taken', $overall->total),
            Stat::make('Pass Rate', ($overall->total ? round($overall->passed / $overall->total * 100) : 100).'%')
                ->description("$overall->passed Passed, $overall->failed Failed, $overall->incomplete Incomplete"),
        ];

        $examOrder = ['OBS', 'TWR', 'APP', 'CTR'];

        foreach ($examOrder as $exam) {
            $results = $examStats->get($exam, collect());
            $examTotal = $results->sum('total');
            $examPassed = $results->firstWhere('result', PracticalResult::PASSED)->total ?? 0;
            $examFailed = $results->firstWhere('result', PracticalResult::FAILED)->total ?? 0;
            $examIncomplete = $results->firstWhere('result', PracticalResult::INCOMPLETE)->total ?? 0;

            $cards[] = Stat::make("{$exam} Pass Rate", ($examTotal ? round($examPassed / $examTotal * 100) : 100).'%')
                ->description("$examTotal Total, $examPassed Passed, $examFailed Failed, $examIncomplete Incomplete");
        }

        return $cards;
    }
}
