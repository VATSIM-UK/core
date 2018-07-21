<?php

namespace App\Http\Controllers\Adm\Operations;

use App\Http\Controllers\Adm\AdmController;
use App\Models\Mship\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

        $startDate = Carbon::parse($request->get('year').'-'.$request->get('quarter'));
        $endDate = Carbon::parse($request->get('year').'-'.$request->get('quarter'))->addMonths(3);

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
            'year' => 'required|numeric|min:2016|max:'.Carbon::now()->year,
        ]);
    }

    private function membersLeftDivision($startDate, $endDate)
    {
        return Account::whereHas('statesHistory', function ($q) use ($startDate, $endDate) {
            $q->where('code', 'DIVISION')->whereBetween('end_at', [$startDate, $endDate]);
        })->count();
    }

    private function pilotsVisiting($startDate, $endDate)
    {
        return Account::whereHas('notes', function ($q) use ($startDate, $endDate) {
            $q->where('content', 'like', '% Pilot Training was accepted%')->whereBetween('created_at', [$startDate, $endDate]);
        })->count();
    }

    private function newJoinersAsFirstDivision($startDate, $endDate)
    {
        return Account::whereHas('statesHistory', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_at', [$startDate, $endDate])->whereColumn('mship_account.created_at', 'start_at')->where('mship_state.id', 3);
        })->whereBetween('joined_at', [$startDate, $endDate])->count();
    }

    private function membersBecomingInactive($startDate, $endDate)
    {
        return Account::whereHas('statesHistory', function ($q) use ($startDate, $endDate) {
            $q->where('mship_state.id', 3)->where(function ($query) use ($startDate, $endDate) {
                $query->whereNotBetween('end_at', [$startDate, $endDate])->orWhere('end_at', null);
            });
        })->whereHas('datachanges', function ($q) use ($startDate, $endDate) {
            $q->where('data_key', 'inactive')->where('data_new', 1)->whereBetween('created_at', [$startDate, $endDate]);
        })->count();
    }

    private function completedTransfersExObs($startDate, $endDate)
    {
        return Account::whereHas('statesHistory', function ($q) use ($startDate, $endDate) {
            $q->where('mship_state.id', 3)->whereNull('end_at')->whereBetween('start_at', [$startDate, $endDate]);
        })->whereHas('statesHistory', function ($q) use ($startDate, $endDate) {
            $q->where('mship_state.id', '!=', 3)->whereBetween('end_at', [$startDate, $endDate]);
        })->whereHas('qualifications', function ($q) {
            $q->whereBetween('mship_qualification.id', [3, 10]);
        })->count();
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
