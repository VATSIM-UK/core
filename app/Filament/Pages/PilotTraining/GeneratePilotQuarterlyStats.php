<?php

namespace App\Filament\Pages\PilotTraining;

use App\Filament\Helpers\Pages\BasePage;
use Carbon\Carbon;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\DB;

class GeneratePilotQuarterlyStats extends BasePage
{
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

    protected static function canUse(): bool
    {
        return true;
        // return auth()->user()->can('operations.access');
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
                ['name' => 'P1 Sessions', 'value' => $this->SessionCount($startDate, $endDate, 'P1_PPL(A)')],
                ['name' => 'P1 Mentoring Sessions', 'value' => $this->SessionCount($startDate, $endDate, 'P1_PPL(A)_MEN')],
                ['name' => 'P1 Exams (total / passes)', 'value' => $this->ExamCount($startDate, $endDate, 'P1_PPL(A)')],
            ],
            'P2' => [
                ['name' => 'P2 Sessions', 'value' => $this->SessionCount($startDate, $endDate, 'P2_PPL(A)')],
                ['name' => 'P2 Mentoring Sessions', 'value' => $this->SessionCount($startDate, $endDate, 'P2_SEIR(A)_MEN')],
                ['name' => 'P2 Exams (total / passes)', 'value' => $this->ExamCount($startDate, $endDate, 'P2_PPL(A)')],
            ],
            'General' => [
                ['name' => 'Unique Students', 'value' => $this->StudentCount($startDate, $endDate)],
            ],
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
            ->whereBetween('taken_date', [$startDate, $endDate])
            ->whereNull('cancelled_datetime')
            ->where('noShow', '=', 0)
            ->get(['position as session_type', 'taken_date as date', 'student_id as student_cid', 'mentor_id as mentor_cid']);

        $csvData = "session_type,date,student_cid,mentor_cid\n";

        foreach ($sessions as $session) {
            $csvData .= "{$session->session_type},".
                        Carbon::parse($session->date)->format('d/m/Y').','.
                        "{$session->student_cid},".
                        "{$session->mentor_cid}\n";
        }

        $this->dispatch('download-csv', csv: $csvData);
    }

    private function SessionCount(Carbon $startDate, Carbon $endDate, string $position)
    {
        return DB::connection('cts')
            ->table('sessions')
            ->whereBetween('taken_date', [$startDate, $endDate])
            ->where('position', '=', $position)
            ->whereNull('cancelled_datetime')
            ->where('noShow', '=', 0)
            ->count();
    }

    private function ExamCount(Carbon $startDate, Carbon $endDate, string $position)
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

    private function StudentCount(Carbon $startDate, Carbon $endDate)
    {
        $sessionStudents = DB::connection('cts')
            ->table('sessions')
            ->whereBetween('taken_date', [$startDate, $endDate])
            ->whereNull('cancelled_datetime')
            ->pluck('student_id')
            ->toArray();

        $examStudents = DB::connection('cts')
            ->table('practical_results')
            ->whereBetween('date', [$startDate, $endDate])
            ->pluck('student_id')
            ->toArray();

        return count(array_unique(array_merge($sessionStudents, $examStudents)));
    }

    private function MentorCount(Carbon $startDate, Carbon $endDate, string $position)
    {
        $sessionMentors = DB::connection('cts')
            ->table('sessions')
            ->whereBetween('taken_date', [$startDate, $endDate])
            ->where('position', '=', $position)
            ->whereNull('cancelled_datetime')
            ->where('noShow', '=', 0)
            ->distinct('mentor_id')
            ->count('mentor_id');

        $examMentors = DB::connection('cts')
            ->table('practical_results')
            ->whereBetween('date', [$startDate, $endDate])
            ->where('exam', '=', $position)
            ->distinct('mentor_id')
            ->count('mentor_id');
    }
}
