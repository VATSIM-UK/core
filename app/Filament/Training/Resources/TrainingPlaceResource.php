<?php

namespace App\Filament\Training\Resources;

use App\Filament\Training\Pages\TrainingPlace\ViewTrainingPlace;
use App\Filament\Training\Resources\TrainingPlaceResource\Pages;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Str;

class TrainingPlaceResource extends Resource
{
    protected static ?string $model = TrainingPlace::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Training Places';

    protected static ?string $navigationGroup = 'Training';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        $categoryGroup = Group::make('trainingPosition.category')
            ->label('Category')
            ->titlePrefixedWithLabel(false)
            ->collapsible()
            ->getTitleFromRecordUsing(
                fn (TrainingPlace $record): string => filled($record->trainingPosition?->category)
                    ? $record->trainingPosition->category
                    : 'Uncategorised'
            )
            ->getKeyFromRecordUsing(
                fn (TrainingPlace $record): string => filled($record->trainingPosition?->category)
                    ? $record->trainingPosition->category
                    : '__uncategorised__'
            )
            ->scopeQueryByKeyUsing(function (Builder $query, string $key): Builder {
                if ($key === '__uncategorised__') {
                    return $query->whereHas('trainingPosition', fn (Builder $query) => $query->whereNull('category')->orWhere('category', ''));
                }

                return $query->whereHas('trainingPosition', fn (Builder $query) => $query->where('category', $key));
            });

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with([
                    'waitingListAccount.account',
                    'waitingListAccount.waitingList',
                    'trainingPosition.position',
                ])
            )
            ->groups([$categoryGroup])
            ->defaultGroup($categoryGroup)
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('waitingListAccount.account.name')
                    ->label('Student')
                    ->searchable(['name_first', 'name_last'])
                    ->sortable()
                    ->url(fn (TrainingPlace $record) => ViewTrainingPlace::getUrl(['trainingPlaceId' => $record->id])),

                Tables\Columns\TextColumn::make('waitingListAccount.account_id')
                    ->label('CID')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Training Start')
                    ->date('d/m/Y')
                    ->sortable()
                    ->summarize(
                        Summarizer::make('average_training_time')
                            ->label('Average training time')
                            ->using(function (QueryBuilder $query): float {
                                $alias = (new TrainingPlace)->getTable();
                                $clone = clone $query;
                                $clone->columns = [new \Illuminate\Database\Query\Expression("AVG(DATEDIFF(NOW(), `{$alias}`.`created_at`)) as avg_days")];

                                return (float) ($clone->value('avg_days') ?? 0.0);
                            })
                            ->formatStateUsing(function (mixed $state): string {
                                $value = $state !== null && $state !== '' ? (float) $state : null;

                                return $value !== null
                                    ? number_format((int) round($value)).' '.Str::plural('day', (int) round($value))
                                    : '—';
                            })
                    ),

                Tables\Columns\TextColumn::make('trainingPosition.position.callsign')
                    ->label('Position')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (TrainingPlace $record): string => $record->deleted_at ? 'deleted' : 'active')
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'danger')
                    ->icon(fn (string $state): string => $state === 'active' ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->formatStateUsing(fn (string $state): string => Str::title($state)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('trainingPosition.category')
                    ->label('Category')
                    ->options(TrainingPosition::all()->pluck('category', 'category')->map(fn ($category) => Str::title($category ?? 'Uncategorised')))
                    ->preload()
                    ->searchable()
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas('trainingPosition', fn (Builder $q): Builder => $q->where('category', $data['value']))
                        : $query),

                Tables\Filters\TrashedFilter::make()
                    ->label('Training Place Status')
                    ->placeholder('Active only')
                    ->trueLabel('Active & removed')
                    ->falseLabel('Removed only'),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (TrainingPlace $record) => url("/training/training-places/{$record->id}")),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->persistColumnSearchesInSession()
            ->paginated(['25', '50', '100'])
            ->defaultPaginationPageOption(25);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrainingPlaces::route('/'),
        ];
    }
}