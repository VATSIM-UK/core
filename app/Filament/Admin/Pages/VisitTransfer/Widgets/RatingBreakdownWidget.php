<?php

namespace App\Filament\Admin\Pages\VisitTransfer\Widgets;

use App\Services\Admin\VisitTransferStats;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RatingBreakdownWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public ?int $year = null;

    public ?int $type = null;

    public ?Carbon $start = null;

    public ?Carbon $end = null;

    public function table(Table $table): Table
    {
        return $table
            ->records(fn () => collect($this->getBreakdown()))
            ->columns([
                TextColumn::make('name')
                    ->label('Rating'),
                TextColumn::make('total')
                    ->label('Total')
                    ->numeric()
                    ->alignCenter(),
                TextColumn::make('accepted')
                    ->label('Accepted')
                    ->numeric()
                    ->color('success')
                    ->alignCenter(),
                TextColumn::make('rejected')
                    ->label('Rejected')
                    ->numeric()
                    ->color('danger')
                    ->alignCenter(),
            ])
            ->paginated(false);
    }

    public function getBreakdown(): array
    {
        $start = $this->start ?? Carbon::create($this->year ?? now()->year, 1, 1)->startOfDay();
        $end = $this->end ?? Carbon::create($this->year ?? now()->year, 12, 31)->endOfDay();

        return VisitTransferStats::byRating($this->type, $start, $end);
    }
}
