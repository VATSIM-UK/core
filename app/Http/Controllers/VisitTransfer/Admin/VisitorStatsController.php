<?php

namespace App\Http\Controllers\VisitTransfer\Admin;

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

    public function index($startDate = null, $endDate = null)
    {
        $accounts = $this->accounts->with(['networkDataAtc', 'qualifications', 'states'])
        ->whereHas('states', function ($query) {
            $query->where('code', '=', 'VISITING');
        })->orderBy('id', 'desc')->get();

        return $this->viewMake('visit-transfer.admin.hours.list')
                ->with('accounts', $accounts);
    }
}
