<?php

declare(strict_types=1);

namespace App\Filament\Training\Resources\TrainingPlaceResource\Widgets;

use App\Models\Training\TrainingPlace\TrainingPlace;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Str;

class TrainingPlaceCategoryChart extends ChartWidget
{
    protected ?string $heading = 'Training Places by Category';

    protected ?string $description = 'Distribution of active training places by category.';

    protected ?string $maxHeight = '200px';

    protected ?string $pollingInterval = null;

    protected bool $isCollapsible = true;

    /**
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => ['display' => false],
                'y' => ['display' => false],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $counts = TrainingPlace::with('trainingPosition')
            ->get()
            ->groupBy(fn (TrainingPlace $place): string => filled($place->trainingPosition?->category)
                ? $place->trainingPosition->category
                : 'Uncategorised')
            ->map->count()
            ->sortDesc();

        $labels = $counts->keys()->map(fn (string $category): string => Str::title($category))->values()->all();
        $data = $counts->values()->all();

        $colors = [
            '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
            '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1',
        ];

        $backgroundColors = $counts->keys()->map(fn (string $_, int $i) => $colors[$i % count($colors)])->values()->all();

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
