<?php

namespace App\Filament\Admin\Resources\VisitTransfer\VisitingRemovals;

use App\Filament\Admin\Resources\VisitTransfer\VisitingRemovals\Pages\ListVisitingRemovals;
use App\Filament\Support\NameColumn;
use App\Models\VisitTransfer\VisitingRemoval;
use App\Services\VisitTransfer\VisitingControllerInactivity;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VisitingRemovalResource extends Resource
{
    protected static ?string $model = VisitingRemoval::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-minus';

    protected static string|\UnitEnum|null $navigationGroup = 'Visiting / Transferring';

    protected static ?string $label = 'Visiting Removals';

    protected static ?string $pluralLabel = 'Visiting Removals';

    protected static ?int $navigationSort = 30;

    public static function canAccess(): bool
    {
        return auth()->user()->can('vt.removal.view.*');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('account_id')->label('CID')->searchable(),
                NameColumn::make('account.name')->label('Name'),
                TextColumn::make('reason')
                    ->label('Reason')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        VisitingControllerInactivity::REASON_SIX_MONTHS => 'Inactive for 6 months',
                        VisitingControllerInactivity::REASON_TWICE_IN_TWO_YEARS => 'Inactive twice in 2 years',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        VisitingControllerInactivity::REASON_TWICE_IN_TWO_YEARS => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('removed_at')->label('Removed')->dateTime()->sortable(),
            ])
            ->defaultSort('removed_at', 'desc')
            ->filters([
                SelectFilter::make('reason')
                    ->label('Reason')
                    ->options([
                        VisitingControllerInactivity::REASON_SIX_MONTHS => 'Inactive for 6 months',
                        VisitingControllerInactivity::REASON_TWICE_IN_TWO_YEARS => 'Inactive twice in 2 years',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVisitingRemovals::route('/'),
        ];
    }
}
