<?php

namespace App\Filament\Training\Resources\TrainingPlaces;

use App\Filament\Training\Pages\TrainingPlace\ViewTrainingPlace;
use App\Filament\Training\Resources\TrainingPlaces\Pages\ListTrainingPlaces;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Str;

class TrainingPlaceResource extends Resource
{
    protected static ?string $model = TrainingPlace::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Training Places';

    protected static string|\UnitEnum|null $navigationGroup = 'Training';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        $categoryGroup = Group::make('category')
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
            ->orderQueryUsing(fn (Builder $query, string $direction): Builder => $query
                ->orderBy(
                    TrainingPosition::query()
                        ->select('category')
                        ->whereColumn('training_positions.id', 'training_places.trainable_id')
                        ->where('training_places.trainable_type', TrainingPosition::class),
                    $direction,
                )
                ->orderByDesc('training_places.created_at')
            )
            ->scopeQueryByKeyUsing(function (Builder $query, string $key): Builder {
                if ($key === '__uncategorised__') {
                    return $query->where(function (Builder $query) {
                        $query->whereHasMorph('trainable', [TrainingPosition::class], function (Builder $query) {
                            $query->whereNull('category')->orWhere('category', '');
                        })
                            ->orWhere('trainable_type', '!=', TrainingPosition::class)
                            ->orWhereNull('trainable_type');
                    });
                }

                return $query->whereHasMorph('trainable', [TrainingPosition::class], fn (Builder $query) => $query->where('category', $key));
            });

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with([
                    'account',
                    'waitingListAccount.account',
                    'waitingListAccount.waitingList',
                    'trainable' => fn (MorphTo $morphTo) => $morphTo->morphWith([TrainingPosition::class => ['position']]),
                ])
            )
            ->groups([$categoryGroup])
            ->defaultGroup($categoryGroup)
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('account.name')
                    ->label('Student')
                    ->searchable(['name_first', 'name_last'])
                    ->sortable()
                    ->url(fn (TrainingPlace $record) => ViewTrainingPlace::getUrl(['trainingPlaceId' => $record->id])),

                TextColumn::make('account_id')
                    ->label('CID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Training Start')
                    ->date('d/m/Y')
                    ->sortable()
                    ->summarize(
                        Summarizer::make('average_training_time')
                            ->label('Average training time')
                            ->using(function (QueryBuilder $query): float {
                                $alias = (new TrainingPlace)->getTable();
                                $clone = clone $query;
                                $clone->columns = [new Expression("AVG(DATEDIFF(NOW(), `{$alias}`.`created_at`)) as avg_days")];

                                return (float) ($clone->value('avg_days') ?? 0.0);
                            })
                            ->formatStateUsing(function (mixed $state): string {
                                $value = $state !== null && $state !== '' ? (float) $state : null;

                                return $value !== null
                                    ? number_format((int) round($value)).' '.Str::plural('day', (int) round($value))
                                    : '—';
                            })
                    ),

                TextColumn::make('display_name')
                    ->label('Position')
                    ->state(fn (TrainingPlace $record): string => $record->display_name)
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHasMorph(
                        'trainable',
                        [TrainingPosition::class],
                        fn (Builder $query) => $query->whereHas('position', fn (Builder $query) => $query->where('callsign', 'like', "%{$search}%"))
                    )),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (TrainingPlace $record): string => $record->deleted_at ? 'inactive' : 'active')
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'danger')
                    ->icon(fn (string $state): string => $state === 'active' ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->formatStateUsing(fn (string $state): string => Str::title($state)),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Category')
                    ->options(TrainingPosition::all()->pluck('category', 'category')->map(fn ($category) => Str::title($category ?? 'Uncategorised')))
                    ->preload()
                    ->searchable()
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHasMorph('trainable', [TrainingPosition::class], fn (Builder $q): Builder => $q->where('category', $data['value']))
                        : $query),

                TrashedFilter::make()
                    ->label('Training Place Status')
                    ->placeholder('Active only')
                    ->trueLabel('Active & Inactive')
                    ->falseLabel('Inactive only'),
            ], layout: FiltersLayout::AboveContent)
            ->recordActions([
                ViewAction::make()
                    ->url(fn (TrainingPlace $record) => url("/training/training-places/{$record->id}")),
                RestoreAction::make(),
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
            'index' => ListTrainingPlaces::route('/'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['account_id', 'account.name_first', 'account.name_last'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return "{$record->account?->name} ({$record->account_id})";
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['account'])->whereNull('deleted_at');
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return ViewTrainingPlace::getUrl(['trainingPlaceId' => $record->id]);
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [$record->trainingPosition?->position?->name];
    }
}
