<?php

namespace App\Http\Controllers\Adm\Operations;

use App\Http\Controllers\Adm\AdmController;
use App\Models\Mship\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QuarterlyStats extends AdmController
{
    public function get()
    {
        return $this->viewMake('adm.ops.qstats');
    }

    public function generate(Request $request)
    {
        $this->generateValidation($request);

        $startDate = Carbon::parse($request->get('year') . '-' . $request->get('quarter'));
        $endDate = Carbon::parse($request->get('year') . '-' . $request->get('quarter'))->addMonths(3);

        $stats = collect([
            ['name' => 'Left Division', 'value' => $this->membersLeftDivision($startDate, $endDate)],
            ['name' => 'Pilots Visiting', 'value' => $this->pilotsVisiting($startDate, $endDate)],
            ['name' => 'New Joiners as First Division', 'value' => $this->newJoinersAsFirstDivision($startDate, $endDate)],
            ['name' => 'Members Becoming Inactive', 'value' => $this->membersBecomingInactive($startDate, $endDate)],
            ['name' => 'Visiting Controllers Above S1', 'value' => $this->visitingControllersAboveS1($startDate, $endDate)],
            ['name' => 'Completed Transfer (Ex OBS)', 'value' => $this->completedTransfersExObs($startDate, $endDate)],
        ]);

        return $this->viewMake('adm.ops.qstats')
            ->with('stats', $stats)
            ->with('quarter', $startDate->quarter)
            ->with('year', $startDate->year);
    }

    private function generateValidation(Request $request)
    {
        return $request->validate([
            'quarter' => [
                'required',
                Rule::in(['01-01', '04-01', '07-01', '10-01']),
            ],
            'year' => 'required|numeric|min:2016|max:' . Carbon::now()->year,
        ]);
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
}
