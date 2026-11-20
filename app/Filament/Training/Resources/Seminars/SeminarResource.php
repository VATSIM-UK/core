<?php

namespace App\Filament\Training\Resources\Seminars;

use App\Filament\Training\Resources\Seminars\Pages\CreateSeminar;
use App\Filament\Training\Resources\Seminars\Pages\EditSeminar;
use App\Filament\Training\Resources\Seminars\Pages\ListSeminars;
use App\Filament\Training\Resources\Seminars\Pages\ViewSeminar;
use App\Filament\Training\Resources\Seminars\RelationManagers\AttendeesRelationManager;
use App\Filament\Training\Resources\Seminars\RelationManagers\InvitationsRelationManager;
use App\Filament\Training\Resources\Seminars\RelationManagers\WaitingListRelationManager;
use App\Models\Training\Seminar\Seminar;
use App\Models\Training\WaitingList;
use App\Policies\Training\SeminarPolicy;
use Carbon\Carbon;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SeminarResource extends Resource
{
    protected static ?string $model = Seminar::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Training';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Seminars';

    protected static ?int $navigationSort = 20;

    public static function canAccess(): bool
    {
        return auth()->user()->can('viewAny', Seminar::class);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create', Seminar::class);
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('update', $record);
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete', $record);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereIn(
            'training_group',
            app(SeminarPolicy::class)->allowedTrainingGroups(auth()->user(), 'view')
        );
    }

    public static function scopeCallout(): Callout
    {
        return Callout::make('Seminar Training Groups')
            ->info()
            ->columnSpanFull()
            ->description(
                'Seminars can be run for the following training groups: '.implode(', ', Seminar::ALLOWED_TRAINING_GROUPS).'. '.
                'The invitation and theory exam reminder copy text used depends on the training group selected.'
            );
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Seminar Details')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')->required()->maxLength(255),

                        Select::make('training_group')
                            ->label('Training Group')
                            ->options(fn () => collect(app(SeminarPolicy::class)->allowedTrainingGroups(auth()->user(), 'manage'))
                                ->mapWithKeys(fn (string $trainingGroup) => [$trainingGroup => $trainingGroup])
                                ->all())
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('waiting_list_id', null)),

                        Select::make('waiting_list_id')
                            ->label('Waiting List')
                            ->options(function (Get $get) {
                                $department = Seminar::TRAINING_GROUP_DEPARTMENTS[$get('training_group')] ?? null;

                                if ($department === null) {
                                    return [];
                                }

                                return WaitingList::query()
                                    ->where('department', $department)
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required(),
                    ]),
                ])->columnSpanFull(),
            Section::make('Schedule')
                ->schema([
                    DatePicker::make('date')->required()->afterOrEqual('today')->minDate(Carbon::today()),
                    TimePicker::make('from')->seconds(false)->required(),
                    TimePicker::make('to')->seconds(false)->required()->after('from'),
                ])->columns(3),
            Section::make('Invitation Settings')
                ->schema([
                    TextInput::make('invitation_expiry_hours')
                        ->label('Invitation Expiry (Hours)')
                        ->integer()
                        ->minValue(1)
                        ->default(84)
                        ->required(),
                    TextInput::make('capacity')->integer()->minValue(1)->required(),
                ])->columns(2),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Seminar Details')
                ->schema([
                    TextEntry::make('name'),
                    TextEntry::make('date')->date(),
                    TextEntry::make('time_range')
                        ->label('Time')
                        ->state(fn ($record) => $record)
                        ->formatStateUsing(function ($record) {
                            if (! $record->from || ! $record->to) {
                                return null;
                            }

                            $from = Carbon::parse($record->from)->format('H:i');
                            $to = Carbon::parse($record->to)->format('H:i');

                            return "{$from} - {$to}";
                        }),
                    TextEntry::make('capacity'),
                ])->columns(4)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('date')
                    ->date()
                    ->sortable(),

                TextColumn::make('time_range')
                    ->label('Time')
                    ->state(fn ($record) => $record)
                    ->formatStateUsing(function ($record) {
                        if (! $record->from || ! $record->to) {
                            return null;
                        }

                        $from = Carbon::parse($record->from)->format('H:i');
                        $to = Carbon::parse($record->to)->format('H:i');

                        return "{$from} - {$to}";
                    }),

                TextColumn::make('capacity')
                    ->counts('attendees')
                    ->label('Capacity')
                    ->state(fn ($record) => "{$record->attendees_count} / {$record->capacity}")
                    ->badge()
                    ->color(fn ($record) => match (true) {
                        $record->attendees_count >= $record->capacity => 'warning',
                        default => 'success',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->state(fn ($record) => $record->closed_at ? 'Closed' : 'Open')
                    ->badge()
                    ->color(fn ($record) => $record->closed_at ? 'danger' : 'success')
                    ->icon(fn ($record) => $record->closed_at ? 'heroicon-o-lock-closed' : 'heroicon-o-check-circle'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'closed' => 'Closed',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['value'] === 'open', fn ($q) => $q->whereNull('closed_at'))
                            ->when($data['value'] === 'closed', fn ($q) => $q->whereNotNull('closed_at'));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            AttendeesRelationManager::class,
            InvitationsRelationManager::class,
            WaitingListRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeminars::route('/'),
            'create' => CreateSeminar::route('/create'),
            'edit' => EditSeminar::route('/{record}/edit'),
            'view' => ViewSeminar::route('/{record}'),
        ];
    }
}
