<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Qualification extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\\Models\\Mship\\Qualification';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'code';

    public static function availableForNavigation(Request $request)
    {
        return false;
    }

    public static function authorizable()
    {
        return true;
    }

    /**
     * Disable the Qualification from being searchable.
     *
     * @return bool
     */
    public static function searchable()
    {
        return false;
    }

    /**
     * Globally disable the ability to edit a Qualification.
     *
     * @return bool
     */
    public function authorizeToUpdate(Request $request)
    {
        return false;
    }

    /**
     * Globally disable the ability to edit an attached Qualification.
     *
     * @return bool
     */
    public function authorizedToUpdateForSerialization(NovaRequest $request)
    {
        return false;
    }

    /**
     * Globally disable the ability to detach on the pivot.
     *
     * @return bool
     */
    public function authorizedToDeleteForSerialization(NovaRequest $request)
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
            Text::make('Type')->resolveUsing(function ($type) {
                return strtoupper($type);
            }),

            Text::make('Name', 'code')->resolveUsing(function ($code) {
                return title_case($code);
            }),

            BelongsToMany::make('Accounts', 'account')->fields(function () {
                return [
                    DateTime::make('Achieved At', 'created_at'),
                ];
            }),
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
