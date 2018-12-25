<?php

namespace App\Http\Controllers\Adm\VisitTransfer;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Mship\Account as Accounts;

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

    public function create()
    {
        return $this->viewMake('visit-transfer.admin.hours.index');
    }

    public function index(Request $request)
    {
        $startDate = new Carbon($request->get('startDate'));
        $endDate = new Carbon($request->get('endDate'));

        $accounts = $this->accounts->with(['networkDataAtc' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('disconnected_at', [$startDate, $endDate]);
        }, 'networkDataAtcUk' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('disconnected_at', [$startDate, $endDate]);
        }, 'states', 'qualifications'])->whereHas('states', function ($query) {
            $query->where('code', '=', 'VISITING');
        })->orderBy('id', 'asc')->paginate(25);

        return $this->viewMake('visit-transfer.admin.hours.list')
                ->with('accounts', $accounts)
                ->with('startDate', $startDate->toDateString())
                ->with('endDate', $endDate->toDateString());
    }
}
