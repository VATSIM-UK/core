<?php

namespace App\Filament\Training\Pages\MyTraining;

use App\Filament\Training\Forms\Components\AvailabilityGrid;
use App\Filament\Training\Pages\MyTraining\Widgets\MyAvailabilityStats;
use App\Models\Cts\Member;
use App\Models\Cts\PositionValidation;
use App\Models\Mship\Account\AvailabilitySetting;
use App\Models\Training\Availability;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Services\Training\Availability\AvailabilityPlanner;
use App\Services\Training\AvailabilityService;
use Carbon\Carbon;
use CodeWithKyrian\FilamentDateRange\Forms\Components\DateRangePicker;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Session;

class MyAvailability extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected string $view = 'filament.training.pages.my-training.my-availability';

    protected static string|\UnitEnum|null $navigationGroup = 'My Training';

    protected static ?string $navigationLabel = 'My Availability';

    protected static ?string $slug = 'my-training/availability';

    protected static ?int $navigationSort = 15;

    public const FROM_HOUR = 8;

    public const TO_HOUR = 22;

    public const MIN_WEEK_OFFSET = -1;

    public ?array $data = [];

    public ?array $slotData = [];

    #[Session]
    public string $mode = 'calendar';

    public int $weekOffset = 0;

    #[Locked]
    public array $visitedDates = [];

    /** @var array<int, int> */
    public array $seededRowIds = [];

    private ?Collection $availabilityRowsCache = null;

    private ?array $seedCache = null;

    private bool $studentIdResolved = false;

    private ?int $studentIdCache = null;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user?->can('training.access')) {
            return false;
        }

        $hasTrainingPlace = TrainingPlace::where('account_id', $user->id)
            ->whereNull('deleted_at')
            ->exists();

        if ($hasTrainingPlace) {
            return true;
        }

        // Interm check for pilot position validations
        $ctsMember = Member::where('cid', $user->id)->first();

        if (! $ctsMember) {
            return false;
        }

        $pilotCallsigns = [
            'P1_PPL(A)',
            'P2_SEIR(A)',
            'P3_CMEL(A)',
            'P4_ATPL(A)',
            'P1_PPL(A)_MEN',
            'P2_SEIR(A)_MEN',
            'P3_CMEL(A)_MEN',
            'TFP_FLIGHT',
        ];

        $hasPilotValidation = PositionValidation::where('member_id', $ctsMember->id)
            ->whereHas('position', function ($query) use ($pilotCallsigns) {
                $query->whereIn('callsign', $pilotCallsigns);
            })
            ->exists();

        if ($hasPilotValidation) {
            return true;
        }

        return false;
    }

    protected function getAvailabilityService(): AvailabilityService
    {
        return app(AvailabilityService::class);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MyAvailabilityStats::class,
        ];
    }

    public function mount(): void
    {
        $this->visitedDates = $this->weekDates(0);

        $this->availabilityRowsCache = null;
        $this->seedCache = null;

        $existing = $this->data['availability'] ?? [];

        $this->form->fill([
            'availability' => $existing ?: $this->savedCells(),
        ]);

        $this->seededRowIds = $this->seededRowIdsForVisitedDates();

        $this->slotForm->fill($this->getDefaultAvailabilityTimes());
    }

    public function updatedMode(): void
    {
        if ($this->mode !== 'calendar') {
            return;
        }

        $this->availabilityRowsCache = null;
        $this->seedCache = null;

        $this->form->fill(['availability' => $this->savedCells()]);

        $this->seededRowIds = $this->seededRowIdsForVisitedDates();
    }

    /**
     * @return array<int, string>
     */
    public function weekDates(int $offset): array
    {
        $start = now()->utc()->startOfWeek()->addWeeks($offset);

        return collect(range(0, 6))
            ->map(fn (int $i) => $start->copy()->addDays($i)->toDateString())
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function visibleWeekDates(): array
    {
        return $this->weekDates($this->weekOffset);
    }

    public function visibleWeekRange(): string
    {
        $dates = $this->visibleWeekDates();

        return Carbon::parse($dates[0], 'UTC')->toPanelDate().' - '.Carbon::parse($dates[6], 'UTC')->toPanelDate();
    }

    public function nextWeek(): void
    {
        $this->goToWeek($this->weekOffset + 1);
    }

    public function previousWeek(): void
    {
        if ($this->weekOffset <= self::MIN_WEEK_OFFSET) {
            return;
        }

        $this->goToWeek($this->weekOffset - 1);
    }

    public function maxAllowedDate(): Carbon
    {
        return AvailabilityService::lastAllowedDate(now()->utc());
    }

    public function maxWeekOffset(): int
    {
        $weekStart = now()->utc()->startOfWeek();

        return (int) floor($weekStart->diffInDays($this->maxAllowedDate()) / 7);
    }

    public function save(): bool
    {
        $studentId = $this->resolveStudentId();

        if (! $studentId) {
            return false;
        }

        $before = $this->availabilityRows()->pluck('id')->all();

        $plan = app(AvailabilityPlanner::class)->plan(
            $this->visitedDates,
            $this->availabilityRows(),
            $this->data['availability'] ?? [],
            now()->utc(),
            $this->seededRowIds,
        );

        $counts = app(AvailabilityService::class)->applyChanges($studentId, $plan->changes);

        if ($counts['refused']) {
            Notification::make()
                ->title('Nothing was saved')
                ->body('Some of this date changed while you were editing it. Refresh to see the latest availability, then try again.')
                ->danger()
                ->send();

            return false;
        }

        $this->availabilityRowsCache = null;
        $this->seedCache = null;

        // No refill, so cells painted mid-save survive; only rows created here join the seeded set.
        $this->seededRowIds = $this->availabilityRows()
            ->pluck('id')
            ->filter(fn (int $id) => in_array($id, $this->seededRowIds, true) || ! in_array($id, $before, true))
            ->values()
            ->all();

        return true;
    }

    /**
     * @return array<int, string>
     */
    public function savedCells(): array
    {
        return $this->seed()['cells'];
    }

    /**
     * @return array<int, string>
     */
    public function lockedCells(): array
    {
        return $this->seed()['lockedCells'];
    }

    public function hasOvernightSlots(): bool
    {
        $from = sprintf('%02d:00', self::FROM_HOUR);
        $to = sprintf('%02d:00', self::TO_HOUR);

        foreach (array_merge($this->savedCells(), $this->lockedCells()) as $cell) {
            $time = substr($cell, 11, 5);

            if ($time < $from || $time >= $to) {
                return true;
            }
        }

        return false;
    }

    /**
     * Padded a day either side to catch rows crossing midnight.
     *
     * @return Collection<int, Availability>
     */
    protected function availabilityRows(): Collection
    {
        if ($this->availabilityRowsCache !== null) {
            return $this->availabilityRowsCache;
        }

        $memberId = $this->resolveStudentId();

        if (! $memberId || $this->visitedDates === []) {
            return $this->availabilityRowsCache = collect();
        }

        $from = Carbon::parse(min($this->visitedDates), 'UTC')->subDay();
        $to = Carbon::parse(max($this->visitedDates), 'UTC')->addDay();

        return $this->availabilityRowsCache = $this->getAvailabilityService()->availabilityRowsFor($memberId, $from, $to);
    }

    /**
     * @return array{cells: array<int, string>, lockedCells: array<int, string>}
     */
    protected function seed(): array
    {
        if ($this->seedCache !== null) {
            return $this->seedCache;
        }

        if ($this->resolveStudentId() === null || $this->visitedDates === []) {
            return $this->seedCache = ['cells' => [], 'lockedCells' => []];
        }

        return $this->seedCache = app(AvailabilityPlanner::class)->seed($this->visitedDates, $this->availabilityRows(), now()->utc());
    }

    /**
     * The planner never removes a row outside this set, so rows added after seeding survive a save.
     *
     * @return array<int, int>
     */
    private function seededRowIdsForVisitedDates(): array
    {
        return $this->availabilityRows()->pluck('id')->all();
    }

    private function resolveStudentId(): ?int
    {
        if ($this->studentIdResolved) {
            return $this->studentIdCache;
        }

        $this->studentIdResolved = true;

        return $this->studentIdCache = $this->getAvailabilityService()->resolveMemberId((int) auth()->id());
    }

    /** Only rows on newly opened dates join the seeded set; see seededRowIdsForVisitedDates(). */
    private function goToWeek(int $offset): void
    {
        $this->weekOffset = min($this->maxWeekOffset(), max(self::MIN_WEEK_OFFSET, $offset));

        $dates = $this->weekDates($this->weekOffset);
        $newDates = array_values(array_diff($dates, $this->visitedDates));

        $this->visitedDates = array_values(array_unique(array_merge($this->visitedDates, $dates)));

        $this->availabilityRowsCache = null;
        $this->seedCache = null;

        if ($newDates === []) {
            return;
        }

        $newCells = array_values(array_filter(
            $this->seed()['cells'],
            fn (string $cell) => in_array(substr($cell, 0, 10), $newDates, true),
        ));

        $existing = $this->data['availability'] ?? [];

        $this->form->fill([
            'availability' => array_values(array_unique(array_merge($existing, $newCells))),
        ]);

        $this->seededRowIds = array_values(array_unique(array_merge(
            $this->seededRowIds,
            $this->availabilityRows()
                ->filter(fn ($row) => in_array($row->startsAt()->format('Y-m-d'), $newDates, true))
                ->pluck('id')
                ->all(),
        )));
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make(fn () => $this->visibleWeekRange())
                    ->description('Click and drag to paint the times you are free, drag over painted slots to erase them. All times are in Zulu.')
                    ->afterHeader([
                        Action::make('previousWeek')
                            ->hiddenLabel()
                            ->tooltip('Previous week')
                            ->icon('heroicon-m-chevron-left')
                            ->color('gray')
                            ->disabled(fn () => $this->weekOffset <= self::MIN_WEEK_OFFSET)
                            ->action(fn () => $this->previousWeek()),
                        Action::make('thisWeek')
                            ->label('This week')
                            ->color('gray')
                            ->disabled(fn () => $this->weekOffset === 0)
                            ->action(fn () => $this->goToWeek(0)),
                        Action::make('nextWeek')
                            ->hiddenLabel()
                            ->tooltip('Next week')
                            ->icon('heroicon-m-chevron-right')
                            ->color('gray')
                            ->disabled(fn () => $this->weekOffset >= $this->maxWeekOffset())
                            ->action(fn () => $this->nextWeek()),
                    ])
                    ->schema([
                        AvailabilityGrid::make('availability')
                            ->hiddenLabel()
                            ->hours(self::FROM_HOUR, self::TO_HOUR)
                            ->dates(fn () => $this->visibleWeekDates())
                            ->lockedCells(fn () => $this->lockedCells())
                            ->overnightOpen(fn () => $this->hasOvernightSlots())
                            ->lastAllowedDate(fn () => $this->maxAllowedDate())
                            ->now(fn () => now()->utc()),
                    ]),
            ])
            ->statePath('data');
    }

    public function pasteWeekAction(): Action
    {
        return Action::make('pasteWeek')
            ->requiresConfirmation()
            ->modalHeading('Paste over this week?')
            ->modalDescription('The availability already painted in this week will be replaced by the copied week.')
            ->modalSubmitActionLabel('Paste')
            ->action(fn () => $this->dispatch('availability-paste-week'));
    }

    protected function getDefaultAvailabilityTimes(): array
    {
        return auth()->user()?->availability_defaults ?? [
            'from' => AvailabilitySetting::DEFAULT_FROM,
            'to' => AvailabilitySetting::DEFAULT_TO,
        ];
    }

    public function defaultAvailabilityTimesAction(): Action
    {
        return Action::make('defaultAvailabilityTimes')
            ->label('Change default times')
            ->icon('heroicon-o-cog-6-tooth')
            ->modalHeading('Default availability times')
            ->modalDescription('These times are pre-filled on the Add Availability form.')
            ->modalSubmitActionLabel('Save')
            ->form([
                Select::make('from')
                    ->label('From')
                    ->required()
                    ->searchable()
                    ->searchPrompt('Type a time (e.g. 18:30) to filter the list')
                    ->options($this->generateTimeOptions())
                    ->optionsLimit(100),

                Select::make('to')
                    ->label('To')
                    ->required()
                    ->searchable()
                    ->searchPrompt('Type a time (e.g. 18:30) to filter the list')
                    ->options($this->generateTimeOptions())
                    ->optionsLimit(100),
            ])
            ->fillForm(fn () => $this->getDefaultAvailabilityTimes())
            ->action(function (array $data): void {
                $user = auth()->user();

                if (! $user) {
                    return;
                }

                $start = Carbon::parse($data['from']);
                $end = Carbon::parse($data['to']);

                if ($start->greaterThan($end)) {
                    Notification::make()
                        ->title('The "From" time must be before the "To" time.')
                        ->danger()
                        ->send();

                    return;
                }

                if ($end->lessThanOrEqualTo($start) || ! $this->getAvailabilityService()->meetsMinimumDuration($start, $end)) {
                    Notification::make()
                        ->title('Default times must be at least '.AvailabilityService::MINIMUM_SLOT_DURATION_MINUTES.' minutes apart.')
                        ->danger()
                        ->send();

                    return;
                }

                $user->setAvailabilityDefaults($data['from'], $data['to']);
                $this->slotForm->fill($this->getDefaultAvailabilityTimes());

                Notification::make()->title('Default availability times saved')->success()->send();
            });
    }

    public function slotForm(Schema $form): Schema
    {
        return $form
            ->schema([
                DateRangePicker::make('date_range')
                    ->label('Date(s)')
                    ->required()
                    ->minDate(now()->startOfDay())
                    ->maxDate(fn () => $this->maxAllowedDate()),

                Select::make('from')
                    ->label('From')
                    ->required()
                    ->searchable()
                    ->allowHtml(false)
                    ->searchPrompt('Type a time (e.g. 18:30) to filter the list')
                    ->options($this->generateTimeOptions())
                    ->optionsLimit(100),

                Select::make('to')
                    ->label('To')
                    ->required()
                    ->searchable()
                    ->allowHtml(false)
                    ->searchPrompt('Type a time (e.g. 18:30) to filter the list')
                    ->options($this->generateTimeOptions())
                    ->optionsLimit(100),
            ])
            ->statePath('slotData');
    }

    public function create(): void
    {
        $data = $this->slotForm->getState();
        $studentId = $this->resolveStudentId();

        if (! $studentId) {
            return;
        }

        $startDate = Carbon::parse($data['date_range']['start']);
        $endDate = Carbon::parse($data['date_range']['end']);

        if ($endDate->copy()->startOfDay()->greaterThan($this->maxAllowedDate())) {
            Notification::make()->title('You can only add availability up to '.AvailabilityService::MAX_FUTURE_DAYS.' days ahead.')->danger()->send();

            return;
        }

        $startTime = Carbon::parse("{$startDate->toDateString()} {$data['from']}", 'UTC');
        $endTime = Carbon::parse("{$startDate->toDateString()} {$data['to']}", 'UTC');

        if (! $this->getAvailabilityService()->meetsMinimumDuration($startTime, $endTime)) {
            Notification::make()
                ->title('Availability slots must be at least '.AvailabilityService::MINIMUM_SLOT_DURATION_MINUTES.' minutes long.')
                ->danger()
                ->send();

            return;
        }

        $ranges = [];

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            $dateString = $date->toDateString();

            $ranges[] = [
                Carbon::parse("{$dateString} {$data['from']}", 'UTC'),
                Carbon::parse("{$dateString} {$data['to']}", 'UTC'),
            ];
        }

        $counts = $this->getAvailabilityService()->addSlots($studentId, $ranges);

        if ($counts['refused']) {
            Notification::make()->title('Nothing was saved, the new slots would overlap existing availability')->danger()->send();

            return;
        }

        $addedCount = $counts['added'];
        $mergedCount = $counts['merged'];

        if ($addedCount > 0 && $mergedCount > 0) {
            Notification::make()->title("Added {$addedCount} slot(s) and expanded {$mergedCount} existing slot(s)")->warning()->send();
        } elseif ($mergedCount > 0) {
            Notification::make()->title("Expanded {$mergedCount} existing slot(s) to cover the new time(s)")->warning()->send();
        } else {
            Notification::make()->title("{$addedCount} availability slot(s) added")->success()->send();
        }

        $this->slotForm->fill($this->getDefaultAvailabilityTimes());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getAvailabilityService()->getFutureAvailabilityQuery(
                $this->getAvailabilityService()->resolveMemberId(auth()->id()) ?? 0
            ))
            ->defaultSort('date')
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->state(fn (Availability $record) => $record->startsAt()->toPanelDateWithWeekday()),

                TextColumn::make('time_window')
                    ->label('Time')
                    ->state(function (Availability $record): string {
                        return $record->startsAt()->format('H:i').' - '.$record->endsAt()->format('H:i');
                    }),

                TextColumn::make('duration')
                    ->label('Duration')
                    ->state(fn (Availability $record) => $this->getAvailabilityService()->formatDuration(
                        $record->startsAt(), $record->endsAt()
                    )),
            ])
            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->modalWidth('md')
                    ->successNotification(null)
                    ->modalSubmitActionLabel('Update')
                    ->mutateRecordDataUsing(function (Availability $record): array {
                        [$start, $end] = $record->bounds();

                        return [
                            'date' => $start->toDateString(),
                            'from' => $start->format('H:i'),
                            'to' => $end->format('H:i'),
                        ];
                    })
                    ->form([
                        DatePicker::make('date')->required()->native(false)->maxDate(fn () => $this->maxAllowedDate()),
                        Select::make('from')->options($this->generateTimeOptions())->required(),
                        Select::make('to')->options($this->generateTimeOptions())->required(),
                    ])
                    ->action(function (Availability $record, array $data): void {
                        $startUtc = Carbon::parse("{$data['date']} {$data['from']}", 'UTC');
                        $endUtc = Carbon::parse("{$data['date']} {$data['to']}", 'UTC');

                        [$valid, $message] = $this->getAvailabilityService()->isSlotValid(
                            $record->student_id, $startUtc, $endUtc, $record->id
                        );

                        if (! $valid) {
                            Notification::make()->title($message)->danger()->send();

                            return;
                        }

                        if ($this->getAvailabilityService()->editSlot($record, $startUtc, $endUtc)['refused']) {
                            Notification::make()->title('This availability slot overlaps with an existing entry.')->danger()->send();

                            return;
                        }

                        Notification::make()->title('Availability updated')->success()->send();
                    }),
                Action::make('delete')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->iconButton()
                    ->requiresConfirmation(false)
                    ->action(fn (Availability $record) => $this->getAvailabilityService()->removeSlots($record->student_id, [$record])),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->using(fn (Collection $records) => $this->getAvailabilityService()->removeSlots($this->resolveStudentId() ?? 0, $records)),
            ])
            ->emptyStateHeading('No availability added yet')
            ->emptyStateDescription('Use the form to add your availability slots.')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(25);
    }

    protected function generateTimeOptions(): array
    {
        $options = [];

        for ($h = 0; $h < 24; $h++) {
            for ($m = 0; $m < 60; $m += 15) {
                $time = sprintf('%02d:%02d', $h, $m);
                $options[$time] = $time;
            }
        }

        return $options;
    }
}
