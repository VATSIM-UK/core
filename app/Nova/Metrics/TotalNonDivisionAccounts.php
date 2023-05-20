<?php

namespace App\Nova\Metrics;

use App\Models\Mship\Account;
use App\Models\Mship\State;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;

class TotalNonDivisionAccounts extends Value
{
    public $name = 'Total Non-Division Members';

    /**
     * Calculate the value of the metric.
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $divisionState = State::findByCode('DIVISION');

        return $this->count($request, Account::whereHas('states', function ($query) use ($divisionState) {
            $query->where('state_id', '!=', $divisionState->id);
        }));
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            30 => '30 Days',
            60 => '60 Days',
            365 => '365 Days',
            'MTD' => 'Month To Date',
            'QTD' => 'Quarter To Date',
            'YTD' => 'Year To Date',
        ];
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        return now()->addMinutes(1440);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'total-non-division-accounts';
    }
}
