<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @codeCoverageIgnore
 */
class Account extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\\Models\\Mship\\Account';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public function title()
    {
        return sprintf('%s %s', $this->name_first, $this->name_last);
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'email', 'name_first', 'name_last',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Text::make('Name')
                ->rules('required', 'max:255'),

            ID::make('CID', 'id')->sortable(),

            Text::make('Primary Email', 'email')
                ->hideWhenCreating()
                ->hideWhenUpdating(),

            Text::make('ATC Rating', function () {
                return $this->qualificationAtc->code;
            })->exceptOnForms(),

            Text::make('Pilot Rating(s)', function () {
                return $this->qualifications_pilot_string;
            })->exceptOnForms(),

            Text::make('Membership State', function () {
                $state = $this->states()->first();

                return sprintf('%s (%s / %s)', ucwords(strtolower($state->code)), $state->pivot->region, $state->pivot->division);
            }),

            BelongsToMany::make('Roles'),
        ];
    }

    /**
     * @param NovaRequest $request
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $request->fullUrlWithQuery(['accounts_order' => 'id', 'accounts_direction' => 'asc']);
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [
            (new Metrics\TotalAccounts),
            (new Metrics\TotalDivisionAccounts),
            (new Metrics\TotalNonDivisionAccounts),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            (new Filters\MembershipState),
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
