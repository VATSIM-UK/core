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
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SeminarResource extends Resource
{
    protected static ?string $model = Seminar::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Training';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Seminars';

    protected static ?int $navigationSort = 20;

    public static function canAccess(): bool
    {
        return auth()->user()->can('training.seminars.view.*');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('training.seminars.manage.*');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Seminar Details')
                ->schema([
                    TextInput::make('name')->required()->maxLength(255),
                    Textarea::make('description')
                        ->rows(5)
                        ->columnSpanFull(),
                    Select::make('waiting_list_id')
                        ->label('Waiting List')
                        ->options(WaitingList::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                ])->columnSpanFull(),
            Section::make('Schedule')
                ->schema([
                    DatePicker::make('date')->required(),
                    TimePicker::make('from')->seconds(false)->required(),
                    TimePicker::make('to')->seconds(false)->required(),
                ])->columns(3),
            Section::make('Invitation Settings')
                ->schema([
                    TextInput::make('invitation_expiry_days')
                        ->label('Invitation Expiry (Days)')
                        ->integer()
                        ->minValue(1)
                        ->default(7)
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
                    TextEntry::make('waitingList.name')->label('Waiting List'),
                    TextEntry::make('description')->columnSpanFull(),
                ])->columns(2),
            Section::make('Schedule & Settings')
                ->schema([
                    TextEntry::make('date')->date('d/m/Y'),
                    TextEntry::make('from'),
                    TextEntry::make('to'),
                    TextEntry::make('capacity'),
                    TextEntry::make('invitation_expiry_days')->label('Invitation Expiry (Days)'),
                ])->columns(3),
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
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('from')
                    ->label('From')
                    ->dateTime('H:i'),

                TextColumn::make('to')
                    ->label('To')
                    ->dateTime('H:i'),

                TextColumn::make('capacity')
                    ->label('Capacity')
                    ->state(fn ($record) => "{$record->attendees()->count()} / {$record->capacity}")
                    ->badge()
                    ->color(fn ($record) => match (true) {
                        $record->attendees()->count() >= $record->capacity => 'warning',
                        default => 'success',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->state(fn ($record) => $record->closed_at ? 'Closed' : 'Open')
                    ->badge()
                    ->color(fn ($record) => $record->closed_at ? 'danger' : 'success')
                    ->icon(fn ($record) => $record->closed_at ? 'heroicon-o-lock-closed' : 'heroicon-o-check-circle'),
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
