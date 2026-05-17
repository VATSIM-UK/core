<?php

namespace App\Livewire\Training;

use App\Models\Cts\Member;
use App\Models\Cts\Session;
use Carbon\Carbon;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Attributes\Url;
use Livewire\Component;

class AvailabilityGantt extends Component implements HasForms
{
    use InteractsWithForms;

    #[Url]
    public string $date;

    #[Url]
    public ?string $category = null;

    public bool $onlyPending = false;

    public ?string $positionFilter = null;

    public function mount()
    {
        $this->date = request()->query('date', Carbon::today()->format('Y-m-d'));
        $this->category = request()->query('category', null);

        if ($this->category && ! auth()->user()->hasMentoringPermissionForCategory($this->category)) {
            $this->category = null;
        }
    }

    public function previousDay()
    {
        $this->date = Carbon::parse($this->date)->subDay()->format('Y-m-d');
    }

    public function nextDay()
    {
        $this->date = Carbon::parse($this->date)->addDay()->format('Y-m-d');
    }

    public function setToday()
    {
        $this->date = Carbon::today()->format('Y-m-d');
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

        if (empty($allowedCallsigns)) {
            return collect();
        }

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
                    ->latest('taken_date')
                    ->limit(1),
            ])
            ->orderByRaw("COALESCE(last_session_date, '1970-01-01') ASC")
            ->limit(7)
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
}
