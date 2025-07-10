<?php

namespace App\Filament\Admin\Pages\Operations;

use App\Filament\Admin\Helpers\Pages\BasePage;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement;
use Carbon\Carbon;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\DB;

class GenerateQuarterlyStats extends BasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Operations';

    protected static string $view = 'filament.pages.operations.generate-quarterly-stats';

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
            'Division Membership' => [
                ['name' => 'Left Division', 'value' => $this->membersLeftDivision($startDate, $endDate)],
                ['name' => 'Pilots Visiting', 'value' => $this->pilotsVisiting($startDate, $endDate)],
                ['name' => 'New Joiners as First Division', 'value' => $this->newJoinersAsFirstDivision($startDate, $endDate)],
                ['name' => 'Members Becoming Inactive', 'value' => $this->membersBecomingInactive($startDate, $endDate)],
                ['name' => 'Visiting Controllers Above S1', 'value' => $this->visitingControllersAboveS1($startDate, $endDate)],
                ['name' => 'Completed Transfer (Ex OBS)', 'value' => $this->completedTransfersExObs($startDate, $endDate)],
            ],
            'Completed Mentoring Sessions' => $this->completedMentoringSessions($startDate, $endDate),
            'Exam Passes' => $this->examPasses($startDate, $endDate),
            'Issued Position Group Endorsements' => $this->issuedPositionGroupEndorsements($startDate, $endDate),
        ]);
    }

    protected static function canUse(): bool
    {
        return auth()->user()->can('operations.access');
    }

    private function membersLeftDivision($startDate, $endDate)
    {
        return DB::table('mship_account_state')
            ->where('state_id', '=', 3)
            ->whereBetween('end_at', [$startDate, $endDate])
            ->count();
    }

    private function pilotsVisiting($startDate, $endDate)
    {
        return Account::whereHas('notes', function ($q) use ($startDate, $endDate) {
            $q->where('content', 'like', '% Pilot Training was accepted%')->whereBetween('created_at', [$startDate, $endDate]);
        })->count();
    }

    private function newJoinersAsFirstDivision($startDate, $endDate)
    {
        return DB::table('mship_account_state')
            ->leftJoin('mship_account', 'mship_account.id', '=', 'mship_account_state.account_id')
            ->where('state_id', '=', 3)
            ->whereBetween('start_at', [$startDate, $endDate])
            ->whereColumn('created_at', 'start_at')
            ->whereBetween('joined_at', [$startDate, $endDate])
            ->count();
    }

    private function membersBecomingInactive($startDate, $endDate)
    {
        return DB::table('mship_account_state')
            ->leftJoin('sys_data_change', 'mship_account_state.account_id', '=', 'sys_data_change.model_id')
            ->where('state_id', '=', 3)
            ->whereRaw('(mship_account_state.end_at > sys_data_change.created_at OR end_at is null)')
            ->where('data_key', '=', 'inactive')
            ->where('data_new', '=', 1)
            ->whereBetween('sys_data_change.created_at', [$startDate, $endDate])
            ->count();
    }

    private function completedTransfersExObs($startDate, $endDate)
    {
        return DB::table('mship_account_state')
            ->where('state_id', '=', 3)
            ->whereNull('end_at')
            ->whereBetween('start_at', [$startDate, $endDate])
            ->whereIn('account_id', function ($states) use ($startDate, $endDate) {
                $states->select('account_id')
                    ->from('mship_account_state')
                    ->whereBetween('end_at', [$startDate, $endDate]);
            })
            ->whereIn('account_id', function ($quals) {
                $quals->select('account_id')
                    ->from('mship_account_qualification')
                    ->whereBetween('qualification_id', [2, 10]);
            })
            ->count();
    }

    private function visitingControllersAboveS1($startDate, $endDate)
    {
        return Account::whereHas('statesHistory', function ($q) use ($startDate, $endDate) {
            $q->where('mship_state.id', 2)->whereBetween('start_at', [$startDate, $endDate]);
        })->whereHas('qualifications', function ($q) {
            $q->whereBetween('mship_qualification.id', [3, 10]);
        })->count();
    }

    private function completedMentoringSessions(Carbon $startDate, Carbon $endDate)
    {
        return DB::connection('cts')
            ->table('sessions')
            ->select('rts.name', DB::raw('count(*) as total'))
            ->join('rts', 'sessions.rts_id', '=', 'rts.id')
            ->whereBetween('taken_date', [$startDate, $endDate])
            ->whereNull('cancelled_datetime')
            ->where('noShow', '=', 0)
            ->groupBy('rts_id')
            ->get()
            ->flatMap(fn ($value) => [['name' => $value->name, 'value' => $value->total]])
            ->toArray();
    }

    private function issuedPositionGroupEndorsements(Carbon $startDate, Carbon $endDate)
    {
        return Endorsement::with('endorsable')
            ->whereBetween('mship_account_endorsement.created_at', [$startDate, $endDate])
            ->join('position_groups', 'position_groups.id', 'mship_account_endorsement.endorsable_id')
            ->groupBy('position_groups.id', 'position_groups.name')
            ->select(['position_groups.name', 'position_groups.id', DB::raw('count(*) as total')])
            ->get()
            ->flatMap(fn ($value) => [['name' => $value->name, 'value' => $value->total]])
            ->toArray();
    }

    private function examPasses(Carbon $startDate, Carbon $endDate)
    {
        return DB::connection('cts')
            ->table('practical_results')
            ->select('exam', DB::raw('count(*) as total'))
            ->whereBetween('date', [$startDate, $endDate])
            ->where('result', '=', 'P')
            ->groupBy('exam')
            ->get()
            ->flatMap(fn ($value) => [['name' => $value->exam, 'value' => $value->total]])
            ->toArray();
    }
}
