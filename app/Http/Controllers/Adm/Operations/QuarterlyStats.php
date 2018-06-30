<?php

namespace App\Http\Controllers\Adm\Operations;

use App\Http\Controllers\Adm\AdmController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class QuarterlyStats extends AdmController
{
    public function getIndex() {
        $start_date = Carbon::now(); // Will change to post request
        $end_date = Carbon::now()->subMonths(6); // Will change to post request

        $leftDivsion = $this->membersLeftDivision($start_date, $end_date);
        $pilotsVisiting = $this->pilotsVisiting($start_date, $end_date);
        $newJoinersAsFirstDivsion = $this->newJoinersAsFirstDivsion($start_date, $end_date);
        $membersBecomingInactive = $this->membersBecomingInactive($start_date, $end_date);
        $visitingControllersAboveS1 = $this->visitingControllersAboveS1($start_date, $end_date);
        $completedTransfersExObs = $this->completedTransfersExObs($start_date, $end_date);
    }

    private function membersLeftDivision($startDate, $endDate) {
        $query = DB::table('mship_account_state')
            ->where('state_id', '=', 3)
            ->whereBetween('end_at', [$startDate, $endDate])
            ->count();

        return $query;
    }

    private function pilotsVisiting($startDate, $endDate) {
        $query = DB::table('mship_account_note')
            ->where('content', 'like', '% - Pilot Training was accepted%')
            ->count();

        return $query;
    }

    private function newJoinersAsFirstDivsion($startDate, $endDate) {
        $query = DB::table('mship_account_state')
            ->leftJoin('mship_account', 'mship_account.id', '=', 'mship_account_state.account_id')
            ->whereBetween('start_at', [$startDate, $endDate])
            ->where('created_at', '=', 'start_at')
            ->whereBetween('joined_at', [$startDate, $endDate])
            ->where('state_id', '=', 3)
            ->count();

        return $query;
    }

    private function membersBecomingInactive($startDate, $endDate) {
        $query = DB::table('mship_account_state')
            ->leftJoin('sys_data_change', 'mship_account_state.account_id', '=', 'sys_data_change.model_id')
            ->where('state_id', '=', 3)
            ->whereNotBetween('end_at', [$startDate, $endDate])
            ->where('data_key', '=', 'inactive')
            ->where('data_new', '=', 1)
            ->whereBetween('sys_data_change.created_at', [$startDate, $endDate])
            ->count();

        return $query;
    }

    private function completedTransfersExObs($startDate, $endDate) {
        $query = DB::table('mship_account_state')
            ->where('state_id', '=', 3)
            ->whereNull('end_at')
            ->whereBetween('start_at', [$startDate, $endDate])
            ->whereIn('account_id', function($states) use ($startDate, $endDate) {
                $states->select('account_id') // too few variables exception thrown
                    ->from('mship_account_state')
                    ->whereBetween('end_at', [$startDate, $endDate]);
            })
            ->whereIn('account_id', function($quals) {
                $quals->select('account_id')
                    ->from('mship_account_qualification')
                    ->whereBetween('qualification_id', [2, 11]);
            })
            ->count();

        return $query;
    }

    private function visitingControllersAboveS1($startDate, $endDate) {
        $query = DB::table('mship_account_state')
            ->where('state_id', '=', 2)
            ->where('start_at', [$startDate, $endDate])
            ->whereIn('account_id', function($quals) {
                $quals->select('account_id')
                    ->from('mship_account_qualification')
                    ->where('qualification_id', '>', 2)
                    ->where('qualification_id', '<', 11);
            })
            ->count();

        return $query;
    }
}
