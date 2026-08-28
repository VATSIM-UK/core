<?php

namespace App\Filament\Admin\Resources\Events;

use App\Filament\Admin\Resources\Events\Pages\CreateEvent;
use App\Filament\Admin\Resources\Events\Pages\EditEvent;
use App\Filament\Admin\Resources\Events\Pages\ListEvents;
use App\Filament\Admin\Resources\Events\Pages\ViewEvent;
use App\Models\Events\Event;
use App\Models\Mship\Account;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    public static function canViewAny(): bool
    {
        return auth()->user()->can('events.view');
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()->can('events.view');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('events.manage');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('events.manage');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('events.manage');
    }

    public static function getFormSchema(): array
    {
        return [
            Section::make('Details')
                ->description('Name, schedule and the positions the event covers.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')->required()->maxLength(191),
                        TextInput::make('tagline')->maxLength(191),
                    ]),
                    RichEditor::make('description')
                        ->extraInputAttributes(['style' => 'min-height: 200px;'])
                        ->columnSpanFull(),
                    TextInput::make('image_url')->label('Banner URL')->url()->columnSpanFull(),
                    Grid::make(2)->schema([
                        DateTimePicker::make('start')
                            ->required()
                            ->native(false)
                            ->minutesStep(15)
                            ->seconds(false)
                            ->helperText('Times in Zulu (UTC).'),
                        DateTimePicker::make('end')
                            ->required()
                            ->after('start')
                            ->native(false)
                            ->minutesStep(15)
                            ->seconds(false)
                            ->helperText('Times in Zulu (UTC).'),
                    ]),
                    Grid::make(2)->schema([
                        Select::make('positions')
                            ->label('Positions')
                            ->relationship('positions', 'callsign')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('The ATC positions the event covers.'),
                        Select::make('manager_id')
                            ->label('Event manager')
                            ->relationship('manager', 'name_first')
                            ->getOptionLabelFromRecordUsing(fn (Account $record): string => "{$record->name_first} {$record->name_last} (CID {$record->id})")
                            ->searchable()
                            ->preload(),
                    ]),
                    Toggle::make('rostered')
                        ->label('Rostered')
                        ->helperText('This will block bookings for the specified positions from being made by members.'),
                ]),
            Section::make('Checklist')
                ->description('Track the prep steps before publishing.')
                ->schema([
                    Grid::make(2)->schema([
                        Toggle::make('eoi_published')->label('EOI Published'),
                        Toggle::make('roster_published')->label('Roster Published'),
                        Toggle::make('briefing_published')->label('Briefing Published'),
                        Toggle::make('briefing_created')->label('Briefing Created'),
                        Toggle::make('banner_created')->label('Banner Created'),
                        Toggle::make('ecfmp_set_up')->label('ECFMP Set Up'),
                        Toggle::make('my_vatsim_published')->label('My.vatsim.net published'),
                    ]),
                    Placeholder::make('published_at')
                        ->label('Published at')
                        ->content(fn (?Event $record): string => $record?->published_at
                            ? $record->published_at->format('d M Y H:i')
                            : '')
                        ->hidden(fn (?Event $record): bool => ! $record?->isPublished()),
                ]),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema(static::getFormSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('start')->dateTime()->sortable(),
                TextColumn::make('end')->dateTime(),
                IconColumn::make('rostered')->boolean(),
                TextColumn::make('positions.callsign')->label('Positions')->badge(),
                TextColumn::make('manager.name_first')
                    ->label('Manager')
                    ->formatStateUsing(fn (Event $record): string => $record->manager
                        ? "{$record->manager->name_first} {$record->manager->name_last}"
                        : ''),
                TextColumn::make('published_at')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ? 'Published' : 'Draft')
                    ->color(fn (?string $state): string => $state ? 'success' : 'gray'),
            ])
            ->filters([
                TernaryFilter::make('published_at')
                    ->label('Published')
                    ->nullable(),
                SelectFilter::make('upcoming')
                    ->options(['upcoming' => 'Upcoming', 'past' => 'Past'])
                    ->query(function ($query, array $data) {
                        if ($data['value'] === 'upcoming') {
                            return $query->where('end', '>=', now());
                        }
                        if ($data['value'] === 'past') {
                            return $query->where('end', '<', now());
                        }

                        return $query;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('start', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEvents::route('/'),
            'create' => CreateEvent::route('/create'),
            'edit' => EditEvent::route('/{record}/edit'),
            'view' => ViewEvent::route('/{record}'),
        ];
    }
}
