<?php

namespace App\Filament\Admin\Pages\VisitTransfer\Widgets;

use App\Services\Admin\VisitTransferStats;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class FacilityBreakdownWidget extends Widget
{
    public ?Carbon $start = null;

    public ?Carbon $end = null;

    public ?int $type = null;

    protected string $view = 'filament.widgets.visit-transfer.facility-breakdown';

    protected int|string|array $columnSpan = 'full';

    public function getRows(): array
    {
        return VisitTransferStats::byFacility($this->type, $this->start, $this->end);

    }
}
