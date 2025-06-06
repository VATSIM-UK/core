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

    protected static ?string $slug = 'test2';

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
            'Division Membership' => [
                ['name' => 'P1 Sessions', 'value' => $this->P1SessionCount($startDate, $endDate)],
            ],
            /*
                ['name' => 'Pilots Visiting', 'value' => $this->pilotsVisiting($startDate, $endDate)],
                ['name' => 'New Joiners as First Division', 'value' => $this->newJoinersAsFirstDivision($startDate, $endDate)],
                ['name' => 'Members Becoming Inactive', 'value' => $this->membersBecomingInactive($startDate, $endDate)],
                ['name' => 'Visiting Controllers Above S1', 'value' => $this->visitingControllersAboveS1($startDate, $endDate)],
                ['name' => 'Completed Transfer (Ex OBS)', 'value' => $this->completedTransfersExObs($startDate, $endDate)],
            ],
            'Completed Mentoring Sessions' => $this->completedMentoringSessions($startDate, $endDate),
            'Exam Passes' => $this->examPasses($startDate, $endDate),
            'Issued Position Group Endorsements' => $this->issuedPositionGroupEndorsements($startDate, $endDate),
            */
        ]);
    }

    private function P1SessionCount(Carbon $startDate, Carbon $endDate)
    {

        return DB::connection('cts')
            ->table('sessions')
            ->whereBetween('taken_date', [$startDate, $endDate])
            ->where('position', '=', 'P1_PPL(A)')
            ->whereNull('cancelled_datetime')
            ->where('noShow', '=', 0)
            ->count();
    }
}
