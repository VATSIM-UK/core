<?php

namespace App\Http\Controllers\Adm\Operations;

use App\Http\Controllers\Adm\AdmController;
use App\Models\Mship\Account;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class QuarterlyStats extends AdmController
{
    public function get()
    {
        return $this->viewMake('adm.ops.qstats');
    }

    public function generate(Request $request)
    {
        $startDate = Carbon::parse($request->get('year').'-'.$request->get('quarter'));
        $endDate = $startDate->addMonths(4);

        $stats = collect([
            'leftDivision' => $this->membersLeftDivision($startDate, $endDate),
            'pilotsVisiting' => $this->pilotsVisiting($startDate, $endDate),
            'newJoinersAsFirstDivision' => $this->newJoinersAsFirstDivision($startDate, $endDate),
            'membersBecomingInactive' => $this->membersBecomingInactive($startDate, $endDate),
            'visitingControllersAboveS1' => $this->visitingControllersAboveS1($startDate, $endDate),
            'completedTransfersExObs' => $this->completedTransfersExObs($startDate, $endDate),
        ]);

        return $this->viewMake('adm.ops.qstats')
                ->with('stats', $stats);
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
