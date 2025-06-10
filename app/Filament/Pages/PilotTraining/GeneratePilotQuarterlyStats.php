<?php

namespace App\Filament\Pages\PilotTraining;

use App\Filament\Helpers\Pages\BasePage;
use Carbon\Carbon;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Support\Facades\DB;

class GeneratePilotQuarterlyStats extends BasePage
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Pilot Training';

    protected static string $view = 'filament.pages.pilot-training.generate-pilot-quarterly-stats';

    protected static ?string $navigationLabel = 'Quarterly Stats';

    public $quarter = null;

    public $year = null;

    public $statistics = null;

    private $quarterMappings = ['01-01' => 'Q1', '04-01' => 'Q2', '07-01' => 'Q3', '10-01' => 'Q4'];

    public function mount(): void
    {
        parent::mount();
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        $yearOptions = range(now()->year, 2016, -1);

        return [
            Grid::make()->schema([
                Select::make('quarter')
                    ->required()
                    ->inOptions()
                    ->options($this->quarterMappings),
                Select::make('year')
                    ->required()
                    ->inOptions()
                    ->options(collect($yearOptions)->mapWithKeys(fn ($year) => [$year => $year])),
            ]),
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $startDate = Carbon::parse($this->year.'-'.$this->quarter);
        $endDate = $startDate->copy()->addMonths(3);

        $this->statistics = collect([
            'P1' => [
                ['name' => 'P1 Sessions', 'value' => $this->sessionCount($startDate, $endDate, 'P1_PPL(A)')],
                ['name' => 'P1 OTS Sessions', 'value' => $this->sessionCount($startDate, $endDate, 'P1_PPL(A)_MEN')],
                ['name' => 'P1 Exams (total / passes)', 'value' => $this->examCount($startDate, $endDate, 'P1')],
            ],
            'P2' => [
                ['name' => 'P2 Sessions', 'value' => $this->sessionCount($startDate, $endDate, 'P2_SEIR(A)')],
                ['name' => 'P2 OTS Sessions', 'value' => $this->sessionCount($startDate, $endDate, 'P2_SEIR(A)_MEN')],
                ['name' => 'P2 Exams (total / passes)', 'value' => $this->examCount($startDate, $endDate, 'P2')],
            ],
            'TFP' => [
                ['name' => 'TFP Sessions', 'value' => $this->sessionCount($startDate, $endDate, 'TFP_FLIGHT')],
            ],
            'General' => [
                ['name' => 'Unique Students', 'value' => $this->studentCount($startDate, $endDate)],
                ['name' => 'Unique Mentors', 'value' => $this->mentorCount($startDate, $endDate)],
            ],
            'P1 Mentor Session Count' => $this->mentorStats($startDate, $endDate, 'P1_PPL(A)')->map(function ($mentor) {
                return [
                    'name' => "{$mentor['name']} ({$mentor['cid']})",
                    'value' => $mentor['session_count'],
                ];
            })->values()->toArray(),
            'P1 Student Session Count' => $this->studentStats($startDate, $endDate, 'P1_PPL(A)')->map(function ($student) {
                return [
                    'name' => "{$student['name']} ({$student['cid']})",
                    'value' => $student['session_count'],
                ];
            })->values()->toArray(),
            'P2 Mentor Session Count' => $this->mentorStats($startDate, $endDate, 'P2_SEIR(A)')->map(function ($mentor) {
                return [
                    'name' => "{$mentor['name']} ({$mentor['cid']})",
                    'value' => $mentor['session_count'],
                ];
            })->values()->toArray(),
            'P2 Student Session Count' => $this->studentStats($startDate, $endDate, 'P2_SEIR(A)')->map(function ($student) {
                return [
                    'name' => "{$student['name']} ({$student['cid']})",
                    'value' => $student['session_count'],
                ];
            })->values()->toArray(),
            'TFP Mentor Session Count' => $this->mentorStats($startDate, $endDate, 'TFP_FLIGHT')->map(function ($mentor) {
                return [
                    'name' => "{$mentor['name']} ({$mentor['cid']})",
                    'value' => $mentor['session_count'],
                ];
            })->values()->toArray(),
            'TFP Student Session Count' => $this->studentStats($startDate, $endDate, 'TFP_FLIGHT')->map(function ($student) {
                return [
                    'name' => "{$student['name']} ({$student['cid']})",
                    'value' => $student['session_count'],
                ];
            })->values()->toArray(),
        ]);
    }

    #[Js]
    public function exportSessionsCsv(): void
    {
        $this->validate();

        $startDate = Carbon::parse($this->year.'-'.$this->quarter);
        $endDate = $startDate->copy()->addMonths(3);

        $sessions = DB::connection('cts')
            ->table('sessions')
            ->join('members as students', 'sessions.student_id', '=', 'students.id')
            ->join('members as mentors', 'sessions.mentor_id', '=', 'mentors.id')
            ->whereBetween('taken_date', [$startDate, $endDate])
            ->whereNull('cancelled_datetime')
            ->where('noShow', '=', 0)
            ->get([
                'position as session_type',
                'taken_date as date',
                'students.cid as student_cid',
                'mentors.cid as mentor_cid',
            ]);

        $csvData = "session_type,date,student_cid,mentor_cid\n";

        foreach ($sessions as $session) {
            $csvData .= "{$session->session_type},".
                        Carbon::parse($session->date)->format('d/m/Y').','.
                        "{$session->student_cid},".
                        "{$session->mentor_cid}\n";
        }

        $quarter = $this->quarterMappings[$this->quarter];
        $this->dispatch('download-csv', filename: "pilot-training_{$this->year}_{$quarter}.csv", csv: $csvData);
    }

    private function sessionCount(Carbon $startDate, Carbon $endDate, string $position)
    {
        return DB::connection('cts')
            ->table('sessions')
            ->whereBetween('taken_date', [$startDate, $endDate])
            ->where('position', '=', $position)
            ->whereNull('cancelled_datetime')
            ->where('noShow', '=', 0)
            ->count();
    }

    private function examCount(Carbon $startDate, Carbon $endDate, string $position)
    {
        $result = DB::connection('cts')
            ->table('practical_results')
            ->selectRaw("
                COUNT(*) as total,
                COALESCE(SUM(CASE WHEN result = 'P' THEN 1 ELSE 0 END), 0) as passes
            ")
            ->whereBetween('date', [$startDate, $endDate])
            ->where('exam', '=', $position)
            ->first();

        return "{$result->total} / {$result->passes}";
    }

    private function studentCount(Carbon $startDate, Carbon $endDate)
    {
        $sessionStudents = DB::connection('cts')
            ->table('sessions')
            ->whereBetween('taken_date', [$startDate, $endDate])
            ->whereNull('cancelled_datetime')
            ->distinct()
            ->pluck('student_id');

        $examStudents = DB::connection('cts')
            ->table('practical_results')
            ->whereBetween('date', [$startDate, $endDate])
            ->distinct()
            ->pluck('student_id');

        return $sessionStudents->merge($examStudents)->unique()->count();
    }

    private function mentorCount(Carbon $startDate, Carbon $endDate)
    {
        return DB::connection('cts')
            ->table('sessions')
            ->whereBetween('taken_date', [$startDate, $endDate])
            ->whereNull('cancelled_datetime')
            ->where('noShow', '=', 0)
            ->distinct('mentor_id')
            ->count('mentor_id');
    }

    private function mentorStats(Carbon $startDate, Carbon $endDate, string $position)
    {
        return DB::connection('cts')
            ->table('sessions')
            ->join('members', 'sessions.mentor_id', '=', 'members.id')
            ->select('mentor_id', 'members.cid', 'members.name', DB::raw('COUNT(*) as session_count'))
            ->whereBetween('taken_date', [$startDate, $endDate])
            ->whereNull('cancelled_datetime')
            ->where('noShow', '=', 0)
            ->where('position', '=', $position)
            ->groupBy('mentor_id', 'members.cid', 'members.name')
            ->orderByDesc('session_count')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->mentor_id => [
                    'cid' => $item->cid,
                    'name' => $item->name,
                    'session_count' => $item->session_count,
                ]];
            });
    }

    private function studentStats(Carbon $startDate, Carbon $endDate, string $position)
    {
        return DB::connection('cts')
            ->table('sessions')
            ->join('members', 'sessions.student_id', '=', 'members.id')
            ->select('student_id', 'members.cid', 'members.name', DB::raw('COUNT(*) as session_count'))
            ->whereBetween('taken_date', [$startDate, $endDate])
            ->whereNull('cancelled_datetime')
            ->where('noShow', '=', 0)
            ->where('position', '=', $position)
            ->groupBy('student_id', 'members.cid', 'members.name')
            ->orderByDesc('session_count')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->student_id => [
                    'cid' => $item->cid,
                    'name' => $item->name,
                    'session_count' => $item->session_count,
                ]];
            });
    }
}
