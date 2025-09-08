<?php

namespace App\Filament\Admin\Pages\PilotTraining;

use App\Filament\Admin\Helpers\Pages\BasePage;
use App\Filament\Pages\PilotTraining\Js;
use App\Services\Admin\PilotTrainingStats;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GeneratePilotQuarterlyStats extends BasePage
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Pilot Training';

    protected string $view = 'filament.pages.pilot-training.generate-pilot-quarterly-stats';

    protected static ?string $navigationLabel = 'Quarterly Stats';

    public ?string $quarter = null;

    public ?string $year = null;

    /** @var Collection|null */
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
                ['name' => 'P1 Sessions', 'value' => PilotTrainingStats::sessionCount($startDate, $endDate, 'P1_PPL(A)')],
                ['name' => 'P1 OTS Sessions', 'value' => PilotTrainingStats::sessionCount($startDate, $endDate, 'P1_PPL(A)_MEN')],
                ['name' => 'P1 Exams (total / passes)', 'value' => PilotTrainingStats::examCount($startDate, $endDate, 'P1')],
            ],
            'P2' => [
                ['name' => 'P2 Sessions', 'value' => PilotTrainingStats::sessionCount($startDate, $endDate, 'P2_SEIR(A)')],
                ['name' => 'P2 OTS Sessions', 'value' => PilotTrainingStats::sessionCount($startDate, $endDate, 'P2_SEIR(A)_MEN')],
                ['name' => 'P2 Exams (total / passes)', 'value' => PilotTrainingStats::examCount($startDate, $endDate, 'P2')],
            ],
            'TFP' => [
                ['name' => 'TFP Sessions', 'value' => PilotTrainingStats::sessionCount($startDate, $endDate, 'TFP_FLIGHT')],
            ],
            'General' => [
                ['name' => 'Unique Students', 'value' => PilotTrainingStats::studentCount($startDate, $endDate)],
                ['name' => 'Unique Mentors', 'value' => PilotTrainingStats::mentorCount($startDate, $endDate)],
            ],
            'P1 Mentor Session Count' => PilotTrainingStats::mentorStats($startDate, $endDate, 'P1_PPL(A)')->map(function ($mentor) {
                return [
                    'name' => "{$mentor['name']} ({$mentor['cid']})",
                    'value' => $mentor['session_count'],
                ];
            })->values()->toArray(),
            'P1 Student Session Count' => PilotTrainingStats::studentStats($startDate, $endDate, 'P1_PPL(A)')->map(function ($student) {
                return [
                    'name' => "{$student['name']} ({$student['cid']})",
                    'value' => $student['session_count'],
                ];
            })->values()->toArray(),
            'P2 Mentor Session Count' => PilotTrainingStats::mentorStats($startDate, $endDate, 'P2_SEIR(A)')->map(function ($mentor) {
                return [
                    'name' => "{$mentor['name']} ({$mentor['cid']})",
                    'value' => $mentor['session_count'],
                ];
            })->values()->toArray(),
            'P2 Student Session Count' => PilotTrainingStats::studentStats($startDate, $endDate, 'P2_SEIR(A)')->map(function ($student) {
                return [
                    'name' => "{$student['name']} ({$student['cid']})",
                    'value' => $student['session_count'],
                ];
            })->values()->toArray(),
            'TFP Mentor Session Count' => PilotTrainingStats::mentorStats($startDate, $endDate, 'TFP_FLIGHT')->map(function ($mentor) {
                return [
                    'name' => "{$mentor['name']} ({$mentor['cid']})",
                    'value' => $mentor['session_count'],
                ];
            })->values()->toArray(),
            'TFP Student Session Count' => PilotTrainingStats::studentStats($startDate, $endDate, 'TFP_FLIGHT')->map(function ($student) {
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
}
