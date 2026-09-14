<?php

namespace App\Filament\Admin\Pages\VisitTransfer\Widgets;

use App\Services\Admin\VisitTransferStats;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ApplicationTrendChart extends ChartWidget
{
    protected ?string $heading = 'Applications Received';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    public ?int $year = null;

    public ?int $type = null;

    public ?Carbon $start = null;

    public ?Carbon $end = null;

    public function getDescription(): ?string
    {
        return $this->start && $this->end ? $this->start->toPanelDate().' to '.$this->end->toPanelDate() : '';
    }

    protected function getData(): array
    {
        $year = $this->year ?? now()->year;

        $start = $this->start ?? Carbon::create($year, 1, 1)->startOfDay();
        $end = $this->end ?? Carbon::create($year, 12, 31)->endOfDay();

        $trend = VisitTransferStats::dailyTrend($this->type, $start, $end);

        return [
            'datasets' => [[
                'data' => array_column($trend, 'total'),
                'fill' => true,
            ]],
            'labels' => array_map(
                fn ($row) => Carbon::parse($row['day'])->format('d. m.'),
                $trend
            ),
        ];
    }

    protected ?array $options = [
        'plugins' => [
            'legend' => [
                'display' => false,
            ],
        ],
        'scales' => [
            'y' => [
                'beginAtZero' => true,
                'ticks' => [
                    'precision' => 0,
                    'stepSize' => 1,
                ],
            ],
        ],
    ];

    protected function getType(): string
    {
        return 'line';
    }
}
