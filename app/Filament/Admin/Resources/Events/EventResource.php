<?php

namespace App\Filament\Admin\Resources\Events;

use App\Enums\EventChecklistItem;
use App\Filament\Admin\Resources\Events\Pages\CreateEvent;
use App\Filament\Admin\Resources\Events\Pages\EditEvent;
use App\Filament\Admin\Resources\Events\Pages\ListEvents;
use App\Filament\Admin\Resources\Events\Pages\ViewEvent;
use App\Models\Events\Event;
use App\Models\Mship\Account;
use App\Rules\QuarterHourRule;
use App\Services\Events\EventService;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    public static function canViewAny(): bool
    {
        return auth()->user()->canAny(['events.view', 'events.manage']);
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()->canAny(['events.view', 'events.manage']);
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
            // Resource form pages default to a two-column schema, which would
            // otherwise strand a lone section in the left-hand column.
            Section::make('Details')
                ->description('Name, schedule and the positions the event covers.')
                ->columnSpanFull()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(191)
                            ->disabled(fn (?Event $record): bool => static::detailsAreLocked($record))
                            ->helperText(fn (?Event $record): ?string => static::lockedHelperText($record)),
                        TextInput::make('tagline')->maxLength(191),
                    ]),
                    RichEditor::make('description')
                        ->extraFieldWrapperAttributes(['class' => 'events-description-editor'])
                        ->columnSpanFull(),
                    TextInput::make('image_url')->label('Banner URL')->url()->columnSpanFull(),
                    Grid::make(2)->schema([
                        DateTimePicker::make('start')
                            ->required()
                            ->native(false)
                            ->minutesStep(15)
                            ->seconds(false)
                            ->displayFormat(Event::DATETIME_FORMAT)
                            ->rule(new QuarterHourRule)
                            ->disabled(fn (?Event $record): bool => static::detailsAreLocked($record))
                            ->helperText(fn (?Event $record): string => static::lockedHelperText($record)
                                ?? 'Times in Zulu (UTC).'),
                        DateTimePicker::make('end')
                            ->required()
                            ->after('start')
                            ->native(false)
                            ->minutesStep(15)
                            ->seconds(false)
                            ->displayFormat(Event::DATETIME_FORMAT)
                            ->rule(new QuarterHourRule)
                            ->disabled(fn (?Event $record): bool => static::detailsAreLocked($record))
                            ->helperText(fn (?Event $record): string => static::lockedHelperText($record)
                                ?? 'Times in Zulu (UTC).'),
                    ]),
                    Grid::make(2)->schema([
                        Select::make('positions')
                            ->label('Positions')
                            ->relationship('positions', 'callsign')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->disabled(fn (?Event $record): bool => static::detailsAreLocked($record))
                            ->helperText(fn (?Event $record): string => static::lockedHelperText($record)
                                ?? 'The ATC positions the event covers.'),
                        Select::make('manager_id')
                            ->label('Event manager')
                            ->relationship(
                                'manager',
                                'name_first',
                                fn (Builder $query): Builder => $query->permission('admin.access'),
                            )
                            ->getOptionLabelFromRecordUsing(fn (Account $record): string => "{$record->name_first} {$record->name_last} ({$record->id})")
                            ->searchable()
                            ->preload()
                            ->helperText('Only staff members with admin access can manage an event.'),
                    ]),
                    Toggle::make('rostered')
                        ->label('Rostered')
                        ->helperText('This will block bookings for the specified positions from being made by members.'),
                ]),
            Section::make('Checklist')
                ->description('Track the prep steps before publishing. Ticking a box saves straight away.')
                ->columnSpanFull()
                ->hiddenOn('create')
                ->schema([
                    CheckboxList::make('checklist')
                        ->hiddenLabel()
                        ->options(EventChecklistItem::options())
                        ->descriptions(fn (?Event $record): array => static::checklistDescriptions($record))
                        ->columns(2)
                        ->bulkToggleable()
                        ->columnSpanFull()
                        // Completions live in their own table, not on the record.
                        ->afterStateHydrated(fn (CheckboxList $component, ?Event $record) => $component->state(
                            $record?->completedChecklistItems() ?? [],
                        ))
                        ->dehydrated(false)
                        ->disabled(fn (): bool => ! auth()->user()->can('events.manage'))
                        ->live()
                        ->afterStateUpdated(function (?Event $record, array $state): void {
                            if ($record === null) {
                                return;
                            }

                            app(EventService::class)->syncChecklist($record, $state, auth()->user());

                            Notification::make()
                                ->title('Checklist updated')
                                ->success()
                                ->send();
                        }),
                    Placeholder::make('published_at')
                        ->label('Published at')
                        ->content(fn (?Event $record): string => static::publishedAtLabel($record) ?? '')
                        ->hidden(fn (?Event $record): bool => ! $record?->isPublished()),
                ]),
        ];
    }

    /**
     * Details members have already seen are frozen once published.
     */
    public static function detailsAreLocked(?Event $record): bool
    {
        return $record?->isPublished() ?? false;
    }

    private static function lockedHelperText(?Event $record): ?string
    {
        return static::detailsAreLocked($record)
            ? 'Locked because this event is published.'
            : null;
    }

    /**
     * @return array<string, string>
     */
    private static function checklistDescriptions(?Event $record): array
    {
        if ($record === null) {
            return [];
        }

        $service = app(EventService::class);
        $descriptions = [];

        foreach (EventChecklistItem::cases() as $item) {
            $label = $service->completionLabel($record->completionFor($item));

            if ($label !== null) {
                $descriptions[$item->value] = $label;
            }
        }

        return $descriptions;
    }

    public static function publishedAtLabel(?Event $record): ?string
    {
        if (! $record?->isPublished()) {
            return null;
        }

        $timestamp = $record->published_at->format(Event::DATETIME_FORMAT);
        $publisher = $record->publisher;

        return $publisher
            ? "{$timestamp} by {$publisher->name} ({$publisher->id})"
            : $timestamp;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema(static::getFormSchema());
    }

    public static function table(Table $table): Table
    {
        $checklistTotal = count(EventChecklistItem::cases());

        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount('checklistCompletions'))
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('start')->dateTime(Event::DATETIME_FORMAT)->sortable(),
                TextColumn::make('end')->dateTime(Event::DATETIME_FORMAT),
                IconColumn::make('rostered')->boolean(),
                TextColumn::make('positions.callsign')
                    ->label('Positions')
                    ->badge()
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('checklist_completions_count')
                    ->label('Checklist')
                    ->badge()
                    ->state(fn (Event $record): string => "{$record->checklist_completions_count}/{$checklistTotal}")
                    ->color(fn (Event $record): string => match (true) {
                        $record->checklist_completions_count === 0 => 'danger',
                        $record->checklist_completions_count === $checklistTotal => 'success',
                        default => 'warning',
                    })
                    ->sortable(),
                TextColumn::make('manager.name_first')
                    ->label('Manager')
                    ->formatStateUsing(fn (Event $record): string => $record->manager
                        ? "{$record->manager->name_first} {$record->manager->name_last}"
                        : ''),
                TextColumn::make('published_at')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (Event $record): string => $record->isPublished() ? 'Published' : 'Draft')
                    ->color(fn (Event $record): string => $record->isPublished() ? 'success' : 'gray')
                    ->tooltip(fn (Event $record): ?string => static::publishedAtLabel($record)),
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
