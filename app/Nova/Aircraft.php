<?php

namespace App\Nova;

use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Aircraft extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Smartcars\Aircraft';

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
        'name',
    ];

    public static $group = 'Smartcars';

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('ICAO')->rules('required', 'string', 'max:4'),

            Text::make('Name')->rules('required', 'string', 'max:12'),

            Text::make('Full Name', 'fullname')->rules('required', 'string', 'max:50'),

            Text::make('Registration')->rules('required', 'string', 'max:5'),

            Number::make('Range', 'range_nm')->rules('nullable', 'numeric', 'max:100000'),

            Number::make('Weight', 'weight_kg')->rules('nullable', 'numeric', 'max:100000'),

            Number::make('Max Passengers')->rules('nullable', 'numeric', 'max:100000'),

            Number::make('Max Cargo (KG)', 'max_cargo_kg')->rules('nullable', 'numeric', 'max:100000'),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
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
