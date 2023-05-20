<?php

namespace App\Nova\Filters;

use App\Models\Mship\Feedback\Form;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class FeedbackForm extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query->whereHas('form', function ($query) use ($value) {
            $query->where('form_id', $value);
        });
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(Request $request)
    {
        return Form::all()->pluck('id', 'name')->mapWithKeys(function ($item, $key) {
            return [$key => $item];
        })->toArray();
    }
}
