<?php

namespace App\Filament\Training\Resources\TrainingShutdowns;

use App\Filament\Training\Resources\TrainingShutdowns\Pages\CreateTrainingShutdown;
use App\Filament\Training\Resources\TrainingShutdowns\Pages\ListTrainingShutdowns;
use App\Models\Mship\Account;
use App\Models\Training\TrainingShutdown;
use App\Models\Training\WaitingList;
use CodeWithKyrian\FilamentDateRange\Forms\Components\DateRangePicker;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TrainingShutdownResource extends Resource
{
    protected static ?string $model = TrainingShutdown::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Training Shutdowns';

    protected static string|\UnitEnum|null $navigationGroup = 'Training Shutdowns';

    protected static ?int $navigationSort = 35;

    public static function canViewAny(): bool
    {
        return self::manageableDepartments(auth()->user()) !== [];
    }

    public static function canCreate(): bool
    {
        return self::manageableDepartments(auth()->user()) !== [];
    }

    public static function canDelete($record): bool
    {
        return self::canManageDepartment($record->department);
    }

    public static function canManageDepartment(string $department): bool
    {
        return in_array($department, self::manageableDepartments(auth()->user()), true);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('department', self::manageableDepartments(auth()->user()));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->heading('Create Training Shutdown')->schema([
                Grid::make(2)->columnSpanFull()->schema([
                    Select::make('department')
                        ->label('Department')
                        ->options(fn (): array => self::departmentOptions(auth()->user()))
                        ->default(fn (): ?string => self::manageableDepartments(auth()->user())[0] ?? null)
                        ->required()
                        ->columnSpan(2),

                    DateRangePicker::make('date_range')
                        ->label('Shutdown period')
                        ->required()
                        ->columnSpanFull(),

                    Textarea::make('reason')
                        ->label('Reason')
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('department')
                    ->label('Department')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::departmentLabel($state)),

                TextColumn::make('begins_at')
                    ->label('Starts')
                    ->date()
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label('Ends')
                    ->date()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Currently Active')
                    ->boolean()
                    ->getStateUsing(fn (TrainingShutdown $record): bool => $record->isActive()),

                TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(60),
            ])
            ->recordActions([
                ActionGroup::make([
                    DeleteAction::make()->label('Remove Shutdown'),
                ]),
            ])
            ->defaultSort('begins_at', 'desc')
            ->emptyStateHeading('No training shutdowns');
    }

    /**
     * @return array<int, string>
     */
    private static function manageableDepartments(?Account $user): array
    {
        if (! $user) {
            return [];
        }

        return array_values(array_filter(
            [WaitingList::ATC_DEPARTMENT, WaitingList::PILOT_DEPARTMENT],
            fn (string $department): bool => $user->hasAnyPermission([
                'training.shutdowns.manage.*',
                "training.shutdowns.manage.{$department}",
            ]),
        ));
    }

    /**
     * @return array<string, string>
     */
    private static function departmentOptions(?Account $user): array
    {
        return collect(self::manageableDepartments($user))
            ->mapWithKeys(fn (string $department): array => [$department => self::departmentLabel($department)])
            ->all();
    }

    private static function departmentLabel(string $department): string
    {
        return match ($department) {
            WaitingList::ATC_DEPARTMENT => 'ATC Training',
            WaitingList::PILOT_DEPARTMENT => 'Pilot Training',
            default => ucfirst($department),
        };
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrainingShutdowns::route('/'),
            'create' => CreateTrainingShutdown::route('/create'),
        ];
    }
}
