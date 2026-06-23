<?php

namespace App\Livewire\Training;

use App\Filament\Actions\AcceptMentoringSessionAction;
use App\Filament\Training\Pages\Mentor\Concerns\RemembersTrainingGroupCategory;
use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Training\Mentoring\MentoringScope;
use App\Services\Training\MentorPermissionService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Attributes\Url;
use Livewire\Component;

class AvailabilityGantt extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;
    use RemembersTrainingGroupCategory;

    #[Url]
    public string $date;

    #[Url]
    public ?string $category = null;

    public bool $onlyPending = false;

    public ?string $positionFilter = null;

    public int $studentsPerPage = 6;

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

    public function getStudentsProperty()
    {
        $targetDate = Carbon::parse($this->date);
        $allowedCallsigns = $this->getAllowedCallsigns();

        return Member::query()
            ->whereHas('sessions', function ($query) use ($allowedCallsigns) {
                $query->whereNull('mentor_id')
                    ->whereNull('filed')
                    ->whereNull('cancelled_datetime')
                    ->whereIn('position', $allowedCallsigns);
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
        return AcceptMentoringSessionAction::make(
            name: 'acceptSession',
            label: 'Accept Session',
            color: 'primary',
            modalHeading: function (array $arguments) {
                $availability = Availability::findOrFail($arguments['availability_id']);
                $student = Member::findOrFail($availability->student_id);

                return "Accept Mentoring Session: {$student->name}";
            },
            modalDescription: function (array $arguments) {
                $availability = Availability::findOrFail($arguments['availability_id']);
                $date = Carbon::parse($availability->date)->format('l, jS F Y');

                return "You are accepting a mentoring request for {$date}. Please confirm the exact start and end times below.";
            },
            modalSubmitActionLabel: 'Accept Session',
            resolveAvailability: fn (array $arguments, $record = null): Availability => Availability::findOrFail($arguments['availability_id']),
            onSuccess: function () { $this->dispatch('session-accepted'); },
        );
    }

    public function getStudentsPerPageProperty(): int
    {
        return self::STUDENTS_PER_PAGE;
    }
}
