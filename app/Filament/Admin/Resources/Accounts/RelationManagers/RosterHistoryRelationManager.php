<?php

namespace App\Filament\Admin\Resources\Accounts\RelationManagers;

use App\Models\NetworkData\Atc;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RosterHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'rosterHistory';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Roster History';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query->with(['removedBy', 'rosterUpdate']);
            })
            ->columns([
                TextColumn::make('original_created_at')
                    ->label('Joined')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Removed')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('removedBy.name')
                    ->label('Removed By')
                    ->placeholder('N/A')
                    ->description(fn ($record) => $record->removedBy?->id ?? null)
                    ->sortable(),
                TextColumn::make('rosterUpdate.period_start')
                    ->label('Roster Update')
                    ->getStateUsing(function ($record) {
                        if ($record->rosterUpdate) {
                            return $record->rosterUpdate->period_start->format('d M Y').' - '.$record->rosterUpdate->period_end->format('d M Y');
                        }

                        return null;
                    })
                    ->placeholder('N/A'),
                TextColumn::make('controlling_hours')
                    ->label('Hours Controlled')
                    ->getStateUsing(function ($record) {
                        $minutes = Atc::where('account_id', $record->account_id)
                            ->isUk()
                            ->whereBetween('connected_at', [$record->original_created_at, $record->created_at])
                            ->sum('minutes_online');

                        if (! $minutes) {
                            return null;
                        }

                        $hours = intdiv($minutes, 60);
                        $remainingMinutes = $minutes % 60;

                        if ($hours > 0) {
                            return $hours.'h '.$remainingMinutes.'m';
                        }

                        return $remainingMinutes.'m';
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
