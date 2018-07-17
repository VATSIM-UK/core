<?php

namespace App\Http\Controllers\VisitTransfer\Admin;

use App\Models\Mship\Account as Accounts;
use Carbon\Carbon;
use Illuminate\Support\Facades\Input;

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

    public function index()
    {
        $inputStartDate = Input::get('startDate');
        $inputEndDate = Input::get('endDate');

        $startDate = $inputStartDate != null ? Carbon::parse($inputStartDate) : Carbon::parse('first day of this month');

        $endDate = $inputEndDate != null ? Carbon::parse($inputEndDate) : Carbon::parse('last day of this month');

        $accounts = $this->accounts->with(['networkDataAtc' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('disconnected_at', [$startDate, $endDate]);
        }, 'qualifications', 'states'])
        ->whereHas('states', function ($query) {
            $query->where('code', '=', 'VISITING');
        })->orderBy('id', 'asc')->paginate(25);

        return $this->viewMake('visit-transfer.admin.hours.list')
                ->with('accounts', $accounts)
                ->with('startDate', $startDate)
                ->with('endDate', $endDate);
    }
}
