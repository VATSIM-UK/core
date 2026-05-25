<?php

namespace App\Livewire\Training;

use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Training\Mentoring\MentoringScope;
use App\Services\Training\MentoringSessionsService;
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
use Livewire\Attributes\Url;
use Livewire\Component;

class AvailabilityGantt extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    #[Url]
    public string $date;

    #[Url]
    public ?string $category = null;

    public bool $onlyPending = false;

    public ?string $positionFilter = null;

    private const STUDENTS_PER_PAGE = 6;

    public int $studentsPage = 1;

    public function mount()
    {
        $this->date = request()->query('date', Carbon::today()->format('Y-m-d'));
        $this->category = request()->query('category', null);

        if ($this->category && ! (auth()->user()?->can('viewCategory', [new MentoringScope, $this->category]) ?? false)) {
            $this->category = null;
        }
    }

    public function previousDay()
    {
        $this->date = Carbon::parse($this->date)->subDay()->format('Y-m-d');
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
        return $this->students->forPage($this->studentsPage, self::STUDENTS_PER_PAGE);
    }

    public function previousStudentsPage(): void
    {
        if ($this->studentsPage > 1) {
            $this->studentsPage--;
        }
    }

    public function nextStudentsPage(): void
    {
        if ($this->studentsPage * self::STUDENTS_PER_PAGE < $this->students->count()) {
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
    }

    public function getAvailableCategoriesProperty(): array
    {
        return auth()->user()->getAvailableMentoringCategories();
    }

    protected function getAllowedCallsigns(): array
    {
        $user = auth()->user();

        return $this->category ? $user->getAssignedCallsignsForCategory($this->category) : $user->getAllAssignedCallsigns();
    }

    public function getStudentsProperty()
    {
        $targetDate = Carbon::parse($this->date);
        $allowedCallsigns = $this->getAllowedCallsigns();

        $hasViewAllPermission = auth()->user()?->can('viewAll', Session::class) ?? false;

        if (empty($allowedCallsigns) && ! $hasViewAllPermission) {
            return collect();
        }

        return Member::query()
            ->whereHas('sessions', function ($query) use ($allowedCallsigns, $hasViewAllPermission) {
                $query->whereNull('mentor_id')
                    ->whereNull('filed')
                    ->whereNull('cancelled_datetime');

                // If doesn't have view all permission, filter strictly by their allowed mentoring callsigns
                if (! $hasViewAllPermission) {
                    $query->whereIn('position', $allowedCallsigns);
                }
            })
            ->whereHas('availabilities', function ($query) use ($targetDate) {
                $query->whereDate('date', $targetDate);
            })
            ->with(['availabilities' => function ($query) use ($targetDate) {
                $query->whereDate('date', $targetDate)
                    ->orderBy('from', 'asc');
            }])
            ->addSelect([
                'pending_position' => Session::select('position')
                    ->whereColumn('student_id', 'members.id')
                    ->whereNull('mentor_id')
                    ->whereNull('filed')
                    ->whereNull('cancelled_datetime')
                    ->limit(1),

                'last_session_date' => Session::select('taken_date')
                    ->whereColumn('student_id', 'members.id')
                    ->whereNotNull('taken_date')
                    ->where('taken_date', '<=', now())
                    ->whereNull('cancelled_datetime')
                    ->latest('taken_date')
                    ->limit(1),
            ])
            ->orderByRaw("COALESCE(last_session_date, '1970-01-01') ASC")
            ->get();
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

        return view('livewire.training.availability-gantt', [
            'students' => $students,
            'hours' => range($startTimelineHour, $endTimelineHour),
            'displayDate' => Carbon::parse($this->date),
        ]);
    }

    public function acceptSessionAction(): Action
    {
        return Action::make('acceptSession')
            ->modalHeading(function (array $arguments) {
                $availability = Availability::find($arguments['availability_id']);
                $student = Member::find($availability->student_id);

                return "Accept Mentoring Session: {$student->name}";
            })
            ->modalDescription(function (array $arguments) {
                $availability = Availability::find($arguments['availability_id']);
                $date = Carbon::parse($availability->date)->format('l, jS F Y');

                return "You are accepting a mentoring request for {$date}. Please confirm the exact start and end times below.";
            })
            ->modalSubmitActionLabel('Accept Session')
            ->form(function (array $arguments) {
                $availability = Availability::find($arguments['availability_id']);
                $student = Member::find($availability->student_id);

                $pendingSession = Session::query()
                    ->where('student_id', $student->id)
                    ->whereNull('mentor_id')
                    ->whereNull('filed')
                    ->whereNull('cancelled_datetime')
                    ->first();

                $minTime = Carbon::parse($availability->from)->format('H:i');
                $maxTime = Carbon::parse($availability->to)->format('H:i');

                $timeOptions = $this->generateTimeOptions($minTime, $maxTime);

                return [
                    Grid::make(3)->schema([
                        Placeholder::make('student_name')
                            ->label('Student Name')
                            ->content($student->name),

                        Placeholder::make('student_cid')
                            ->label('Student CID')
                            ->content($student->cid),

                        Placeholder::make('position')
                            ->label('Position')
                            ->content($pendingSession?->position),
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
                            ->default($minTime)
                            ->optionsLimit(100),

                        Select::make('taken_to')
                            ->label('End')
                            ->required()
                            ->searchable()
                            ->allowHtml(false)
                            ->searchPrompt('Type a time (e.g. 18:30) to filter the list')
                            ->options($timeOptions)
                            ->default($maxTime)
                            ->optionsLimit(100),
                    ]),
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
                ];
            })
            ->action(function (array $data, array $arguments, MentoringSessionsService $mentoringService, Component $livewire) {
                $availability = Availability::find($arguments['availability_id']);
                $student = Member::find($availability->student_id);
                $formattedDate = Carbon::parse($availability->date)->format('d/m/Y');

                $success = $mentoringService->acceptSession($arguments['availability_id'], auth()->id(), $data['taken_from'], $data['taken_to']);

                if ($success) {
                    Notification::make()
                        ->title('Session Accepted Successfully')
                        ->body("You are now assigned to mentor {$student->name} on {$formattedDate} from {$data['taken_from']} to {$data['taken_to']}.")
                        ->success()
                        ->send();

                    $livewire->dispatch('session-accepted');
                } else {
                    Notification::make()
                        ->title('Booking Failed')
                        ->body("We could not find an active pending session request for {$student->name} ({$student->cid}). It may have been cancelled or already picked up.")
                        ->danger()
                        ->send();
                }
            });
    }

    public function getStudentsPerPageProperty(): int
    {
        return self::STUDENTS_PER_PAGE;
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
