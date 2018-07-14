<?php

namespace App\Http\Controllers\VisitTransfer\Admin;

use App\Models\Mship\Account as Accounts;
use Carbon\Carbon;

class VisitorStatsController extends \App\Http\Controllers\Adm\AdmController
{
    /**
     * @var Accounts
     */
    protected $accounts;

    public function __construct(Accounts $accounts)
    {
        parent::__construct();

        $this->accounts = $accounts;
    }

    public function index(Carbon $startDate = null, Carbon $endDate = null)
    {
        if (!isset($startDate)) {
            $startDate = Carbon::parse('first day of this month');
        }

        if (!isset($endDate)) {
            $endDate = Carbon::parse('last day of this month');
        }

        $accounts = $this->accounts->with(['networkDataAtc' => function ($query) use ($startDate, $endDate) {
            $query->where('disconnected_at', '>', $startDate);
        }, 'qualifications', 'states'])
        ->whereHas('states', function ($query) {
            $query->where('code', '=', 'VISITING');
        })->orderBy('id', 'desc')->get();

        return $this->viewMake('visit-transfer.admin.hours.list')
                ->with('accounts', $accounts)
                ->with('startDate', $startDate)
                ->with('endDate', $endDate);
    }
}
