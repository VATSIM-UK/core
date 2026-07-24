<?php

namespace App\Filament\Admin\Pages\VisitTransfer\Widgets;

use App\Services\Admin\VisitTransferStats;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class FacilityBreakdownWidget extends TableWidget
{
    public ?Carbon $start = null;

    public ?Carbon $end = null;

    public ?int $type = null;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->records(fn () => collect(
                VisitTransferStats::byFacility($this->type, $this->start, $this->end)
            ))
            ->columns([
                TextColumn::make('name')
                    ->label('Facility'),
                TextColumn::make('total')
                    ->label('Total')
                    ->alignCenter(),
                TextColumn::make('accepted')
                    ->label('Accepted')
                    ->color('success')
                    ->alignCenter(),
                TextColumn::make('rejected')
                    ->label('Rejected')
                    ->color('danger')
                    ->alignCenter(),
            ])
            ->paginated(false);
    }
}
