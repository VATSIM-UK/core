<?php

namespace App\Filament\Admin\Pages\VisitTransfer\Widgets;

use App\Services\Admin\VisitTransferStats;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class RatingBreakdownWidget extends Widget
{
    protected string $view = 'filament.widgets.visit-transfer.rating-breakdown';

    protected int|string|array $columnSpan = 'full';

    public ?int $year = null;

    public ?int $type = null;

    public ?Carbon $start = null;

    public ?Carbon $end = null;

    public function getBreakdown(): array
    {
        $start = $this->start ?? Carbon::create($this->year ?? now()->year, 1, 1)->startOfDay();
        $end = $this->end ?? Carbon::create($this->year ?? now()->year, 12, 31)->endOfDay();

        return VisitTransferStats::byRating($this->type, $start, $end);
    }
}
