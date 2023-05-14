<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Panel;

class Pirep extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Smartcars\Pirep';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    public static $group = 'Smartcars';

    public static $with = ['bid'];

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [];

    /**
     * Removes Pirep from navigation bar.
     *
     * @return bool
     */
    public static function availableForNavigation(Request $request)
    {
        return false;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Textarea::make('Route', 'route')->alwaysShow(),

            Number::make('Landing Rate'),

            Number::make('Fuel Used'),

            Text::make('Account'),

            Text::make('Flight Time'),

            new Panel('Log & Comments', [
                Textarea::make('Comments'),
                Textarea::make('Log'),
            ]),

            new Panel('Approval Information', [
                Boolean::make('Passed'),
                DateTime::make('Failed At', function () {
                    return ! $this->passed;
                })->onlyOnDetail()->canSee(function () {
                    return ! $this->passed;
                }),
                Text::make('Fail Reason', function () {
                    return $this->pass_reason;
                })->onlyOnDetail()->canSee(function () {
                    return ! is_null($this->failed_at);
                }),
            ]),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
