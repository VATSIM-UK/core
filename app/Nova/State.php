<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class State extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\\Models\\Mship\\State';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    public static function availableForNavigation(Request $request)
    {
        return false;
    }

    public static function authorizable()
    {
        return true;
    }

    /**
     * Disable the State from being searchable.
     *
     * @return bool
     */
    public static function searchable()
    {
        return false;
    }

    /**
     * Globally disable the ability to edit a State.
     *
     * @return bool
     */
    public function authorizeToUpdate(Request $request)
    {
        return false;
    }

    /**
     * Globally disable the ability to edit an attached State.
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
            Text::make('State', 'name'),

            BelongsToMany::make('Accounts', 'account')->fields(function () {
                return [
                    Text::make('Region')->resolveUsing(function () {
                        return $this->pivot->region;
                    }),

                    Text::make('Division')->resolveUsing(function () {
                        return $this->pivot->division;
                    }),

                    DateTime::make('Start', 'start_at'),

                    DateTime::make('End', 'end_at'),
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
