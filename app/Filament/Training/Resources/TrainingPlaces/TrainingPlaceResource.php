<?php

namespace App\Filament\Training\Resources\TrainingPlaces;

use App\Filament\Training\Pages\TrainingPlace\ViewTrainingPlace;
use App\Filament\Training\Resources\TrainingPlaces\Pages\ListTrainingPlaces;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use App\Policies\TrainingPlacePolicy;
use App\Services\Training\MentorPermissionService;
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        /** @var Account|null $user */
        $user = auth()->user();

        $canViewAtc = self::canViewDepartment($user, WaitingList::ATC_DEPARTMENT);
        $canViewPilot = self::canViewDepartment($user, WaitingList::PILOT_DEPARTMENT);

        if ($canViewAtc && $canViewPilot) {
            return $query;
        }

        if ($canViewAtc) {
            return $query->where('trainable_type', TrainingPosition::class);
        }

        if ($canViewPilot) {
            return $query->where('trainable_type', Qualification::class);
        }

        return $query->whereRaw('0 = 1');
    }

    public static function table(Table $table): Table
    {
        $categoryGroup = Group::make('category')
            ->label('Category')
            ->titlePrefixedWithLabel(false)
            ->collapsible()
            ->getTitleFromRecordUsing(
                fn (TrainingPlace $record): string => filled($record->category)
                    ? $record->category
                    : 'Uncategorised'
            )
            ->getKeyFromRecordUsing(
                fn (TrainingPlace $record): string => filled($record->category)
                    ? $record->category
                    : '__uncategorised__'
            )
            ->orderQueryUsing(function (Builder $query, string $direction): Builder {
                $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

                $qualificationCases = collect(MentorPermissionService::PILOT_CATEGORY_QUALIFICATION_MAP)
                    ->map(fn (): string => 'WHEN ? THEN ?')
                    ->implode(' ');

                $bindings = [TrainingPosition::class];
                foreach (MentorPermissionService::PILOT_CATEGORY_QUALIFICATION_MAP as $category => $code) {
                    $bindings[] = $code;
                    $bindings[] = $category;
                }
                $bindings[] = Qualification::class;

                return $query
                    ->orderByRaw(
                        "COALESCE(
                            (
                                SELECT category FROM training_positions
                                WHERE training_positions.id = training_places.trainable_id
                                AND training_places.trainable_type = ?
                            ),
                            (
                                SELECT CASE code {$qualificationCases} END
                                FROM mship_qualification
                                WHERE mship_qualification.id = training_places.trainable_id
                                AND training_places.trainable_type = ?
                            )
                        ) {$direction}",
                        $bindings
                    )
                    ->orderByDesc('training_places.created_at');
            })
            ->scopeQueryByKeyUsing(function (Builder $query, string $key): Builder {
                if ($key === '__uncategorised__') {
                    $mappedCodes = array_values(MentorPermissionService::PILOT_CATEGORY_QUALIFICATION_MAP);

                    return $query->where(function (Builder $query) use ($mappedCodes) {
                        $query->whereHasMorph('trainable', [TrainingPosition::class], function (Builder $query) {
                            $query->whereNull('category')->orWhere('category', '');
                        })
                            ->orWhereHasMorph('trainable', [Qualification::class], function (Builder $query) use ($mappedCodes) {
                                $query->whereNotIn('code', $mappedCodes);
                            })
                            ->orWhereNull('trainable_type');
                    });
                }

                if (in_array($key, MentorPermissionService::pilotCategories(), true)) {
                    $code = MentorPermissionService::PILOT_CATEGORY_QUALIFICATION_MAP[$key] ?? null;

                    return $query->whereHasMorph(
                        'trainable',
                        [Qualification::class],
                        fn (Builder $query) => $query->where('code', $code)
                    );
                }

                return $query->whereHasMorph(
                    'trainable',
                    [TrainingPosition::class],
                    fn (Builder $query) => $query->where('category', $key)
                );
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
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where(function (Builder $query) use ($search) {
                        $query->whereHasMorph(
                            'trainable',
                            [TrainingPosition::class],
                            fn (Builder $query) => $query->whereHas(
                                'position',
                                fn (Builder $query) => $query->where('callsign', 'like', "%{$search}%")
                            )
                        )->orWhereHasMorph(
                            'trainable',
                            [Qualification::class],
                            fn (Builder $query) => $query
                                ->where('code', 'like', "%{$search}%")
                                ->orWhere('name_long', 'like', "%{$search}%")
                        );
                    })),

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
                    ->options(fn (): array => self::categoryFilterOptions())
                    ->preload()
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (! filled($value)) {
                            return $query;
                        }

                        if (in_array($value, MentorPermissionService::pilotCategories(), true)) {
                            $code = MentorPermissionService::PILOT_CATEGORY_QUALIFICATION_MAP[$value] ?? null;

                            return $query->whereHasMorph(
                                'trainable',
                                [Qualification::class],
                                fn (Builder $query): Builder => $query->where('code', $code)
                            );
                        }

                        return $query->whereHasMorph(
                            'trainable',
                            [TrainingPosition::class],
                            fn (Builder $query): Builder => $query->where('category', $value)
                        );
                    }),

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

    /**
     * @return array<string, string>
     */
    protected static function categoryFilterOptions(): array
    {
        /** @var Account|null $user */
        $user = auth()->user();
        $canViewAtc = self::canViewDepartment($user, WaitingList::ATC_DEPARTMENT);
        $canViewPilot = self::canViewDepartment($user, WaitingList::PILOT_DEPARTMENT);

        $positionCategories = $canViewAtc
            ? TrainingPosition::query()
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->distinct()
                ->orderBy('category')
                ->pluck('category', 'category')
            : collect();

        $pilotCategories = $canViewPilot
            ? collect(MentorPermissionService::pilotCategories())
                ->mapWithKeys(fn (string $category): array => [$category => $category])
            : collect();

        return $positionCategories
            ->union($pilotCategories)
            ->map(fn (string $category): string => Str::title($category))
            ->all();
    }

    private static function canViewDepartment(?Account $user, string $department): bool
    {
        if (! $user) {
            return false;
        }

        return app(TrainingPlacePolicy::class)->canViewDepartment($user, $department);
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
        return [$record->display_name];
    }
}
