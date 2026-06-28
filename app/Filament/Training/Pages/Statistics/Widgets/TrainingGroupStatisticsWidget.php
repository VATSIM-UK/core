<?php

declare(strict_types=1);

namespace App\Filament\Training\Pages\Statistics\Widgets;

use App\Services\Training\TrainingGroupStatisticsService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class TrainingGroupStatisticsWidget extends StatsOverviewWidget
{
    public string $category = '';

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = null;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        if ($this->category === '') {
            return [];
        }

        $statistics = app(TrainingGroupStatisticsService::class)->statisticsForCategory($this->category);

        return [
            Stat::make('Active Training Places', Number::format($statistics['active_training_places'], precision: 0))
                ->icon('heroicon-o-academic-cap'),

            Stat::make('Avg. Sessions to Rating', $this->formatDecimal($statistics['average_sessions_to_rating']))
                ->icon('heroicon-o-chart-bar'),

            Stat::make('Avg. Training Duration', $this->formatDuration($statistics['average_training_duration_days']))
                ->icon('heroicon-o-clock'),

            Stat::make('Exam First Pass Rate', $this->formatPercentage($statistics['exam_first_pass_rate']))
                ->icon('heroicon-o-trophy'),
        ];
    }

    private function formatDecimal(?float $value): string
    {
        return $value !== null ? number_format($value, 1) : 'N/A';
    }

    private function formatDuration(?float $days): string
    {
        return $days !== null ? number_format($days, 0).' days' : 'N/A';
    }

    private function formatPercentage(?int $value): string
    {
        return $value !== null ? $value.'%' : 'N/A';
    }
}
