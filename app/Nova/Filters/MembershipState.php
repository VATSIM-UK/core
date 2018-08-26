<?php

namespace App\Nova\Filters;

use App\Models\Mship\State;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class MembershipState extends Filter
{
    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query->whereHas('states', function ($query) use ($value) {
            $query->where('state_id', $value);
        });
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return [
            'Division' => State::findByCode('DIVISION')->id,
            'Visiting' => State::findByCode('VISITING')->id,
            'Region' => State::findByCode('REGION')->id,
            'International' => State::findByCode('INTERNATIONAL')->id,
        ];
    }
}
