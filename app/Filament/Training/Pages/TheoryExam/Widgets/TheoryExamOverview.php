<?php

namespace App\Filament\Training\Pages\TheoryExam\Widgets;

use App\Models\Cts\TheoryResult;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class TheoryExamOverview extends BaseWidget
{
    protected function getCards(): array
    {
        $total = TheoryResult::count();
        $passed = TheoryResult::where('pass', true)->count();
        $failed = TheoryResult::where('pass', false)->count();

        $examStats = TheoryResult::select('exam', 'pass', DB::raw('count(*) as total'))
            ->groupBy('exam', 'pass')
            ->get()
            ->groupBy('exam');

        $cards = [
            Stat::make('Total Taken', $total),
            Stat::make('Pass Rate', ($total ? round($passed / $total * 100) : 100).'%')->description("$passed Passed, $failed Failed"),
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
