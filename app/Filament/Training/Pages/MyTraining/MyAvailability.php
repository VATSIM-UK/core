<?php

namespace App\Filament\Training\Pages\MyTraining;

use App\Filament\Training\Forms\Components\AvailabilityGrid;
use App\Filament\Training\Pages\MyTraining\Widgets\MyAvailabilityStats;
use App\Livewire\Training\MyAcceptedMentoringSessionsTable;
use App\Models\Cts\Member;
use App\Models\Cts\PositionValidation;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Services\Training\Availability\AvailabilityPlanner;
use App\Services\Training\AvailabilityService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;

class MyAvailability extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|\UnitEnum|null $navigationGroup = 'My Training';

    protected static ?string $navigationLabel = 'My Availability';

    protected static ?string $slug = 'my-training/availability';

    protected static ?int $navigationSort = 15;

    public const FROM_HOUR = 8;

    public const TO_HOUR = 22;

    public ?array $data = [];

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
        if ($this->weekOffset === 0) {
            return;
        }

        $this->goToWeek($this->weekOffset - 1);
    }

    public function save(): void
    {
        $studentId = $this->resolveStudentId();

        if (! $studentId) {
            return;
        }

        $plan = app(AvailabilityPlanner::class)->plan(
            $this->visitedDates,
            $this->availabilityRows(),
            $this->data['availability'] ?? [],
            now()->utc(),
            $this->seededRowIds,
        );

        $counts = app(AvailabilityService::class)->applyChanges($studentId, $plan->changes);

        $this->notifySave($counts, $plan->skippedShortRuns);

        $this->availabilityRowsCache = null;
        $this->seedCache = null;

        $this->form->fill(['availability' => $this->savedCells()]);

        $this->seededRowIds = $this->seededRowIdsForVisitedDates();
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
     * @return Collection<int, \App\Models\Cts\Availability>
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
        $this->weekOffset = max(0, $offset);

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
                ->filter(fn ($row) => in_array($row->date->format('Y-m-d'), $newDates, true))
                ->pluck('id')
                ->all(),
        )));
    }

    /**
     * @param  array{added: int, edited: int, merged: int, removed: int, refused: bool}  $counts
     */
    private function notifySave(array $counts, int $skipped): void
    {
        if ($counts['refused']) {
            Notification::make()
                ->title('Nothing was saved')
                ->body('Some of this date changed while you were editing it. Refresh to see the latest availability, then try again.')
                ->danger()
                ->send();

            return;
        }

        $parts = [];

        if ($counts['added'] > 0) {
            $parts[] = $counts['added'].' added';
        }

        if ($counts['edited'] > 0) {
            $parts[] = $counts['edited'].' updated';
        }

        if ($counts['merged'] > 0) {
            $parts[] = $counts['merged'].' merged';
        }

        if ($counts['removed'] > 0) {
            $parts[] = $counts['removed'].' removed';
        }

        if ($parts === [] && $skipped === 0) {
            $title = 'No changes to save';
        } elseif ($skipped === 1) {
            $title = 'Availability saved - 1 selection was under '.AvailabilityService::MINIMUM_SLOT_DURATION_MINUTES.' minutes';
        } elseif ($skipped > 1) {
            $title = "Availability saved - {$skipped} selections were under ".AvailabilityService::MINIMUM_SLOT_DURATION_MINUTES.' minutes';
        } else {
            $title = 'Availability saved';
        }

        $notification = Notification::make()
            ->title($title)
            ->success();

        if ($parts !== []) {
            $notification->body(implode(', ', $parts));
        }

        $notification->send();
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
                            ->disabled(fn () => $this->weekOffset === 0)
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
                            ->action(fn () => $this->nextWeek()),
                    ])
                    ->footer([
                        Action::make('save')
                            ->label('Save availability')
                            ->action(fn () => $this->save()),
                    ])
                    ->schema([
                        AvailabilityGrid::make('availability')
                            ->hiddenLabel()
                            ->hours(self::FROM_HOUR, self::TO_HOUR)
                            ->dates(fn () => $this->visibleWeekDates())
                            ->lockedCells(fn () => $this->lockedCells())
                            ->overnightOpen(fn () => $this->hasOvernightSlots())
                            ->now(fn () => now()->utc()),
                    ]),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedSchema::make('form'),
            Livewire::make(MyAcceptedMentoringSessionsTable::class),
        ]);
    }
}
