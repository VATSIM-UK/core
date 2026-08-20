<?php

namespace App\Livewire\Training;

use App\Filament\Training\Pages\Mentor\Concerns\RemembersTrainingGroupCategory;
use App\Models\Cts\Availability;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Training\Mentoring\MentoringScope;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Services\Training\MentoringSessionsService;
use App\Services\Training\MentorPermissionService;
use App\Services\Training\TrainingPlaceService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;

class AvailabilityGantt extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;
    use RemembersTrainingGroupCategory;

    public const STUDENTS_PER_PAGE = 6;

    #[Url]
    public string $date;

    #[Url]
    public ?string $category = null;

    public bool $onlyPending = false;

    public ?string $positionFilter = null;

    public int $studentsPerPage = self::STUDENTS_PER_PAGE;

    public int $studentsPage = 1;

    public function mount()
    {
        $this->date = max(request()->query('date', Carbon::today()->format('Y-m-d')), Carbon::today()->format('Y-m-d'));
        $this->category = request()->query('category', null);

        if ($this->category && ! (auth()->user()?->can('viewCategory', [new MentoringScope, $this->category]) ?? false)) {
            $this->category = null;
        }
    }

    public function previousDay()
    {
        $previous = Carbon::parse($this->date)->subDay();

        if ($previous->isBefore(Carbon::today())) {
            return;
        }

        $this->date = $previous->format('Y-m-d');
        $this->studentsPage = 1;
    }

    public function nextDay()
    {
        $this->date = Carbon::parse($this->date)->addDay()->format('Y-m-d');
        $this->studentsPage = 1;
    }

    public function setToday()
    {
        $this->date = Carbon::today()->format('Y-m-d');
        $this->studentsPage = 1;
    }

    public function getPagedStudentsProperty()
    {
        return $this->students->forPage($this->studentsPage, $this->studentsPerPage);
    }

    public function previousStudentsPage(): void
    {
        if ($this->studentsPage > 1) {
            $this->studentsPage--;
        }
    }

    public function nextStudentsPage(): void
    {
        if ($this->studentsPage * $this->studentsPerPage < $this->students->count()) {
            $this->studentsPage++;
        }
    }

    public function updatedDate(): void
    {
        $this->studentsPage = 1;
    }

    public function updatedCategory(): void
    {
        $this->studentsPage = 1;

        $this->saveCategoryToSession();
    }

    public function getAvailableCategoriesProperty(): array
    {
        return auth()->user()->getAvailableMentoringCategories();
    }

    protected function getAllowedCallsigns(): array
    {
        $user = auth()->user();

        if ($user?->can('viewAll', Session::class) ?? false) {
            $service = app(MentorPermissionService::class);

            if ($this->category) {
                return $service->getAllCtsCallsignsForCategory($this->category);
            }

            return $service->getAllCtsCallsignsForCategories($user->getAvailableMentoringCategories());
        }

        return $this->category ? $user->getAssignedCallsignsForCategory($this->category) : $user->getAllAssignedCallsigns();
    }

    /**
     * Active training places a mentor may book against for the current allow-list.
     *
     * @return Collection<int, TrainingPlace>
     */
    protected function eligibleTrainingPlaces(): Collection
    {
        $allowedCallsigns = $this->getAllowedCallsigns();

        if ($allowedCallsigns === []) {
            return collect();
        }

        return TrainingPlace::query()
            ->with([
                'trainable',
                'account',
                'leaveOfAbsences' => fn ($query) => $query->current(),
            ])
            ->get()
            ->filter(fn (TrainingPlace $place) => $this->isTrainingPlaceBookable($place, $allowedCallsigns))
            ->values();
    }

    /**
     * @param  array<int, string>  $allowedCallsigns
     */
    protected function isTrainingPlaceBookable(TrainingPlace $place, array $allowedCallsigns): bool
    {
        if ($place->leaveOfAbsences->isNotEmpty()) {
            return false;
        }

        $placeCallsigns = $place->trainableCtsPositions();

        if (! array_intersect($placeCallsigns, $allowedCallsigns)) {
            return false;
        }

        if ($this->hasPendingExamForPlace($place)) {
            return false;
        }

        $member = Member::query()->where('cid', $place->account_id)->first();

        if (! $member) {
            return false;
        }

        return ! $this->hasFutureBookedSession($member->id, $placeCallsigns);
    }

    protected function hasPendingExamForPlace(TrainingPlace $place): bool
    {
        // Pending exams are only modelled for ATC training positions today.
        if (! $place->trainingPosition) {
            return false;
        }

        return app(TrainingPlaceService::class)->hasPendingExam($place);
    }

    /**
     * @param  array<int, string>  $callsigns
     */
    protected function hasFutureBookedSession(int $memberId, array $callsigns): bool
    {
        if ($callsigns === []) {
            return false;
        }

        return Session::query()
            ->where('student_id', $memberId)
            ->whereIn('position', $callsigns)
            ->whereNotNull('taken_date')
            ->where('taken_date', '>=', now()->toDateString())
            ->where('session_done', 0)
            ->whereNull('cancelled_datetime')
            ->exists();
    }

    public function getStudentsProperty()
    {
        $targetDate = Carbon::parse($this->date);
        $places = $this->eligibleTrainingPlaces();

        if ($places->isEmpty()) {
            return collect();
        }

        $placeByCid = $places->keyBy('account_id');
        $accountIds = $placeByCid->keys()->all();

        return Member::query()
            ->whereIn('cid', $accountIds)
            ->whereHas('availabilities', function ($query) use ($targetDate) {
                $query->whereDate('date', $targetDate);
            })
            ->with(['availabilities' => function ($query) use ($targetDate) {
                $query->whereDate('date', $targetDate)
                    ->orderBy('from', 'asc');
            }])
            ->addSelect([
                'last_session_date' => Session::selectRaw("CONCAT(taken_date, ' ', COALESCE(taken_from, '00:00:00'))")
                    ->whereColumn('student_id', 'members.id')
                    ->whereNotNull('taken_date')
                    ->whereNull('cancelled_datetime')
                    ->orderBy('taken_date', 'desc')
                    ->orderBy('taken_from', 'desc')
                    ->limit(1),
            ])
            ->orderByRaw("COALESCE(last_session_date, '1970-01-01 00:00:00') ASC")
            ->get()
            ->each(function (Member $member) use ($placeByCid) {
                /** @var TrainingPlace|null $place */
                $place = $placeByCid->get($member->cid);
                $member->setAttribute('primary_position', $place?->primaryCtsPosition());
                $member->setAttribute('training_place_id', $place?->id);
            });
    }

    public function render()
    {
        $students = $this->students;

        $minHour = 24;
        $maxHour = 0;

        foreach ($students as $student) {
            foreach ($student->availabilities as $avail) {
                $startHour = (int) Carbon::parse($avail->from)->format('G');
                $endHour = (int) Carbon::parse($avail->to)->format('G');

                if ($startHour < $minHour) {
                    $minHour = $startHour;
                }
                if ($endHour > $maxHour) {
                    $maxHour = $endHour;
                }
            }
        }

        if ($minHour === 24) {
            $minHour = 7;
            $maxHour = 19;
        }

        $startTimelineHour = max(0, $minHour - 1);
        $endTimelineHour = min(23, $maxHour + 1);

        $nowLinePercent = null;

        if (Carbon::parse($this->date)->isToday()) {
            $now = now();
            $nowMinutesFromMidnight = $now->hour * 60 + $now->minute;
            $timelineStartMinutes = $startTimelineHour * 60;
            $totalTimelineMinutes = ($endTimelineHour - $startTimelineHour + 1) * 60;
            $relativeNowMinutes = $nowMinutesFromMidnight - $timelineStartMinutes;

            $nowLinePercent = max(0, min(100, ($relativeNowMinutes / $totalTimelineMinutes) * 100));
        }

        return view('livewire.training.availability-gantt', [
            'students' => $students,
            'hours' => range($startTimelineHour, $endTimelineHour),
            'displayDate' => Carbon::parse($this->date),
            'nowLinePercent' => $nowLinePercent,
        ]);
    }

    public function acceptSessionAction(): Action
    {
        return Action::make('acceptSession')
            ->modalHeading(function (array $arguments) {
                $availability = Availability::findOrFail($arguments['availability_id']);
                $student = Member::findOrFail($availability->student_id);

                return "Create Mentoring Session: {$student->name}";
            })
            ->modalDescription(function (array $arguments) {
                $availability = Availability::findOrFail($arguments['availability_id']);
                $date = Carbon::parse($availability->date)->format('l, jS F Y');

                return "You are creating a mentoring session for {$date}. Please choose a position and confirm the exact start and end times below.";
            })
            ->modalSubmitActionLabel('Create Session')
            ->form(function (array $arguments) {
                $availability = Availability::findOrFail($arguments['availability_id']);
                $student = Member::findOrFail($availability->student_id);
                $place = $this->trainingPlaceForStudentCid((int) $student->cid);
                $positionOptions = $this->bookablePositionOptions($place);
                $defaultPosition = $place?->primaryCtsPosition();

                if ($defaultPosition && ! array_key_exists($defaultPosition, $positionOptions)) {
                    $defaultPosition = array_key_first($positionOptions);
                }

                $minTime = Carbon::parse($availability->from)->format('H:i');
                $maxTime = Carbon::parse($availability->to)->format('H:i');
                $timeOptions = $this->generateTimeOptions($minTime, $maxTime);

                if (Carbon::parse($availability->date)->isToday()) {
                    $nowTime = now()->format('H:i');
                    $timeOptions = array_filter($timeOptions, fn ($time) => $time >= $nowTime, ARRAY_FILTER_USE_KEY);
                }

                return [
                    Grid::make(3)->schema([
                        Placeholder::make('student_name')
                            ->label('Student Name')
                            ->content($student->name),

                        Placeholder::make('student_cid')
                            ->label('Student CID')
                            ->content($student->cid),

                        Select::make('position')
                            ->label('Position')
                            ->required()
                            ->options($positionOptions)
                            ->default($defaultPosition)
                            ->live()
                            ->searchable(),
                    ]),

                    Grid::make(2)->schema([
                        Select::make('taken_from')
                            ->label('Start')
                            ->required()
                            ->searchable()
                            ->live()
                            ->allowHtml(false)
                            ->searchPrompt('Type a time (e.g. 18:30) to filter the list')
                            ->options($timeOptions)
                            ->default(array_key_first($timeOptions))
                            ->optionsLimit(100),

                        Select::make('taken_to')
                            ->label('End')
                            ->required()
                            ->searchable()
                            ->allowHtml(false)
                            ->searchPrompt('Type a time (e.g. 18:30) to filter the list')
                            ->after('taken_from')
                            ->options(function (Get $get) use ($timeOptions) {
                                $startTime = $get('taken_from');

                                if (! $startTime) {
                                    return $timeOptions;
                                }

                                [$startH, $startM] = explode(':', $startTime);
                                $startMinutes = (int) $startH * 60 + (int) $startM;
                                $minEndMinutes = $startMinutes + 45;

                                return collect($timeOptions)
                                    ->filter(function ($label, $key) use ($minEndMinutes) {
                                        [$h, $m] = explode(':', $key);
                                        $keyMinutes = (int) $h * 60 + (int) $m;

                                        return $keyMinutes >= $minEndMinutes;
                                    })
                                    ->toArray();
                            })
                            ->default(array_key_last($timeOptions))
                            ->optionsLimit(100),
                    ]),

                    Callout::make('slot_in_past')
                        ->heading('This availability slot is in the past')
                        ->description('The student\'s availability window for this slot has already expired. You won\'t be able to create a session during this slot.')
                        ->danger()
                        ->visible(function () use ($availability) {
                            $slotEnd = Carbon::parse($availability->date)
                                ->setTimeFromTimeString(Carbon::parse($availability->to)->format('H:i'));

                            return $slotEnd->isBefore(now());
                        }),

                    Callout::make('24_hours_notice')
                        ->heading('This session is being booked with less than 24 hours notice')
                        ->description('Please contact the student via Discord to confirm their attendance.')
                        ->warning()
                        ->visible(function (Get $get) use ($availability) {
                            $selectedTime = $get('taken_from');
                            if (! $selectedTime) {
                                return false;
                            }

                            $sessionStart = Carbon::parse($availability->date)->setTimeFromTimeString($selectedTime);

                            return $sessionStart->isAfter(now()) && now()->diffInHours($sessionStart, false) < 24;
                        }),

                    Callout::make('overlapping_booking')
                        ->heading(function (Get $get) use ($availability) {
                            $overlap = $this->getOverlappingBooking($get, $availability);

                            if (! $overlap) {
                                return '';
                            }

                            return app(MentoringSessionsService::class)->overlapHeading($overlap);
                        })
                        ->description(function (Get $get) use ($availability) {
                            $overlap = $this->getOverlappingBooking($get, $availability);

                            if (! $overlap) {
                                return '';
                            }

                            return app(MentoringSessionsService::class)->overlapDescription($overlap);
                        })
                        ->danger()
                        ->visible(function (Get $get) use ($availability) {
                            return $this->getOverlappingBooking($get, $availability) !== null;
                        }),
                ];
            })
            ->action(function (array $data, array $arguments, MentoringSessionsService $mentoringService) {
                $availability = Availability::findOrFail($arguments['availability_id']);
                $student = Member::findOrFail($availability->student_id);
                $formattedDate = Carbon::parse($availability->date)->format('d/m/Y');
                $place = $this->trainingPlaceForStudentCid((int) $student->cid);

                if (! $place) {
                    Notification::make()
                        ->title('Booking Failed')
                        ->body('We could not find an eligible training place for this student.')
                        ->danger()
                        ->send();

                    return;
                }

                $from = Carbon::parse($data['taken_from']);
                $to = Carbon::parse($data['taken_to']);

                if ($from->diffInMinutes($to) < 45) {
                    Notification::make()
                        ->title('Session Too Short')
                        ->body('The session must be at least 45 minutes long.')
                        ->danger()
                        ->send();

                    return;
                }

                try {
                    $success = $mentoringService->createSession(
                        $place,
                        $availability,
                        auth()->user(),
                        $data['position'],
                        $data['taken_from'],
                        $data['taken_to'],
                    );
                } catch (\Throwable $e) {
                    Notification::make()
                        ->title('Booking Failed')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                if ($success) {
                    Notification::make()
                        ->title('Session Created Successfully')
                        ->body("You have created a session to mentor {$student->name} on {$formattedDate} from {$data['taken_from']} to {$data['taken_to']}.")
                        ->success()
                        ->send();

                    $this->dispatch('session-accepted');

                    return;
                }

                Notification::make()
                    ->title('Booking Failed')
                    ->body('We could not create a mentoring session for this slot.')
                    ->danger()
                    ->send();
            });
    }

    public function getStudentsPerPageProperty(): int
    {
        return self::STUDENTS_PER_PAGE;
    }

    protected function trainingPlaceForStudentCid(int $cid): ?TrainingPlace
    {
        return $this->eligibleTrainingPlaces()->firstWhere('account_id', $cid);
    }

    /**
     * @return array<string, string>
     */
    protected function bookablePositionOptions(?TrainingPlace $place): array
    {
        if (! $place) {
            return [];
        }

        $options = array_values(array_intersect(
            $place->trainableCtsPositions(),
            $this->getAllowedCallsigns(),
        ));

        return array_combine($options, $options) ?: [];
    }

    protected function getOverlappingBooking(Get $get, Availability $availability): Session|ExamBooking|null
    {
        $takenFrom = $get('taken_from');
        $takenTo = $get('taken_to');
        $position = $get('position');

        if (! $takenFrom || ! $takenTo || ! $position) {
            return null;
        }

        return app(MentoringSessionsService::class)->checkForOverlappingBookings(
            $position,
            $availability->date,
            $takenFrom,
            $takenTo,
        );
    }

    protected function generateTimeOptions(?string $minTime = null, ?string $maxTime = null): array
    {
        $options = [];

        $minMinutes = $minTime ? (int) substr($minTime, 0, 2) * 60 + (int) substr($minTime, 3, 2) : 0;
        $maxMinutes = $maxTime ? (int) substr($maxTime, 0, 2) * 60 + (int) substr($maxTime, 3, 2) : 1440;

        for ($h = 0; $h < 24; $h++) {
            for ($m = 0; $m < 60; $m += 15) {
                $currentMinutes = $h * 60 + $m;

                if ($currentMinutes >= $minMinutes && $currentMinutes <= $maxMinutes) {
                    $time = sprintf('%02d:%02d', $h, $m);
                    $options[$time] = $time;
                }
            }
        }

        if ($minTime && ! isset($options[$minTime])) {
            $options[$minTime] = $minTime;
        }

        if ($maxTime && ! isset($options[$maxTime])) {
            $options[$maxTime] = $maxTime;
        }

        ksort($options);

        return $options;
    }
}
