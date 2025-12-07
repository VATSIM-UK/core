<?php

namespace App\Filament\Training\Pages\Exam\Widgets;

use App\Models\Cts\PracticalResult;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ExamOverview extends BaseWidget
{
    protected function getCards(): array
    {
        $total = PracticalResult::count();
        $passed = PracticalResult::where('result', PracticalResult::PASSED)->count();
        $failed = PracticalResult::where('result', PracticalResult::FAILED)->count();
        $incomplete = PracticalResult::where('result', PracticalResult::INCOMPLETE)->count();

        $examStats = PracticalResult::select('exam', 'result', DB::raw('count(*) as total'))
            ->groupBy('exam', 'result')
            ->get()
            ->groupBy('exam');

        $cards = [
            Stat::make('Total Taken', $total),
            Stat::make('Pass Rate', ($total ? round($passed / $total * 100) : 100).'%')->description("$passed Passed, $failed Failed, $incomplete Incomplete"),
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
