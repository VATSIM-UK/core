<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class Airport extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Smartcars\Airport';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name', 'icao',
    ];

    public static $group = 'Smartcars';

    /**
     * Removes Airport from navigation bar.
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

            Text::make('ICAO')->rules('required', 'string', 'max:4')
                ->creationRules('unique:smartcars_airport,icao')
                ->updateRules('unique:smartcars_airport,icao,{{resourceId}}')
                ->sortable(),

            Text::make('Name')->rules('required', 'string', 'max:100'),

            Text::make('Country')->rules('required', 'string', 'max:50'),

            Text::make('Latitude')->rules('required', 'numeric', 'min:-90', 'max:90')
                ->help('Enter in a decimal format e.g. 52.3456'),

            Text::make('Longitude')->rules('required', 'numeric', 'min:-180', 'max:180')
                ->help('Enter in a decimal format e.g. -1.7374'),
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
