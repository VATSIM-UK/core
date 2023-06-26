<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class Ban extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\\Models\\Mship\\Account\\Ban';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    public static $group = 'Membership';

    /**
     * Removes Ban from navigation bar.
     *
     * @return bool
     */
    public static function availableForNavigation(Request $request)
    {
        return false;
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Bans are processed using an action and thus the form for creating is not required.
     *
     * @return bool
     */
    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
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
            ID::make(),

            BelongsTo::make('Account', 'account')->onlyOnIndex(),

            DateTime::make('Banned At', 'created_at')->exceptOnForms(),

            Text::make('Internal Note', 'reason_extra')->exceptOnForms()->hideFromIndex(),

            BelongsTo::make('Ban Reason', 'reason', 'App\\Nova\\BanReason')->exceptOnForms()->hideFromIndex(),

            BelongsTo::make('Banned By', 'banner', 'App\\Nova\\Account')->exceptOnForms(),

            Boolean::make('Active', 'isActive')->exceptOnForms(),
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
        return [
            new Actions\Mship\RepealBan,
        ];
    }
}
