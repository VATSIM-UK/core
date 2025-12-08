<?php

namespace App\Filament\Training\Pages\TheoryExam\Widgets;

use App\Models\Cts\TheoryResult;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class TheoryExamOverview extends BaseWidget
{
    protected ?string $heading = 'Theory Exam Overview for Current Year';

    protected function getCards(): array
    {
        $startofYear = now()->startOfYear();
        $endofYear = now()->endOfYear();
        $overall = TheoryResult::select(
            DB::raw('count(*) as total'),
            DB::raw('SUM(CASE WHEN pass = 1 THEN 1 ELSE 0 END) as passed'),
            DB::raw('SUM(CASE WHEN pass = 0 THEN 1 ELSE 0 END) as failed')
        )
            ->whereBetween('started', [$startofYear, $endofYear])
            ->first();

        $examStats = TheoryResult::whereBetween('started', [$startofYear, $endofYear])
            ->select('exam', 'pass', DB::raw('count(*) as total'))
            ->groupBy('exam', 'pass')
            ->get()
            ->groupBy('exam');

        $cards = [
            Stat::make('Total Taken', $overall->total),
            Stat::make('Pass Rate', ($overall->total ? round($overall->passed / $overall->total * 100) : 100).'%')->description("$overall->passed Passed, $overall->failed Failed"),
        ];

        $examOrder = ['S1', 'S2', 'S3', 'C1'];

        foreach ($examOrder as $exam) {
            $results = $examStats->get($exam, collect());
            $examTotal = $results->sum('total');
            $examPassed = $results->firstWhere('pass', true)->total ?? 0;
            $examFailed = $results->firstWhere('pass', false)->total ?? 0;

            $cards[] = Stat::make("{$exam} Pass Rate", ($examTotal ? round($examPassed / $examTotal * 100) : 100).'%')
                ->description("$examTotal Total, $examPassed Passed, $examFailed Failed");
        }

        return $cards;
    }
}
