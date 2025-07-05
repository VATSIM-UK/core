<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Resources\SoloEndorsementResource\Pages;
use App\Models\Atc\Position;
use App\Models\Mship\Account\Endorsement;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SoloEndorsementResource extends Resource
{
    protected static ?string $model = Endorsement::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Solo Endorsements';

    protected static ?string $navigationGroup = 'Mentoring';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('endorsable_type', Position::class)->whereNotNull('expires_at');
    }

    /**
     * Overriding here as this is a specialisation of the Endorsement model
     * with a filtered eloquent query
     * and thus using the model policy might have unintended consequences.
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasAnyPermission('endorsement.view.*');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('account.id')->label('CID'),
                Tables\Columns\TextColumn::make('account.name')->label('Account'),
                Tables\Columns\TextColumn::make('endorsable.description')->label('Position'),
                Tables\Columns\TextColumn::make('duration')->getStateUsing(fn ($record) => $record->expires_at->diffInDays($record->created_at).' days')->label('Duration'),
                Tables\Columns\TextColumn::make('created_at')->label('Started At')->isoDateTimeFormat('lll'),
                Tables\Columns\TextColumn::make('expires_at')->label('Expires At')->isoDateTimeFormat('lll')->sortable(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->getStateUsing(fn ($record) => $record->expires_at->isPast() ? 'Expired' : 'Active')
                    ->color(
                        fn (string $state): string => match ($state) {
                            'Expired' => 'danger',
                            'Active' => 'success',
                            default => 'primary',
                        }
                    ),
            ])
            ->defaultSort('expires_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('expires_at')
                    ->label('Endorsement Expiry Status')
                    ->trueLabel('Active')
                    ->default(true)
                    ->falseLabel('Expired')
                    ->nullable()
                    ->placeholder('All endorsements')
                    ->queries(
                        true: fn (Builder $query) => $query->where('expires_at', '>', now()),
                        false: fn (Builder $query) => $query->where('expires_at', '<=', now()),
                        blank: fn (Builder $query) => $query
                    ),

                Tables\Filters\QueryBuilder::make()
                    ->constraints([
                        Tables\Filters\QueryBuilder\Constraints\TextConstraint::make('account.id')->operators(
                            [
                                Tables\Filters\QueryBuilder\Constraints\TextConstraint\Operators\EqualsOperator::class,
                            ]
                        ),
                    ]),
            ], layout: FiltersLayout::AboveContent);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Admin\Resources\SoloEndorsementResource\Pages\ListSoloEndorsements::route('/'),
        ];
    }
}
