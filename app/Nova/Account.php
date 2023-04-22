<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphToMany;
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

    public static $group = 'Membership';

    /**
     * Removes Account from navigation bar.
     *
     * @param  Request  $request
     * @return bool
     */
    public static function availableForNavigation(Request $request)
    {
        return false;
    }

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
     * Custom array to define which models should not be attachable to an Account by default.
     *
     * @var array
     */
    public static $disallowAttach = [
        'App\Models\Mship\Qualification',
        'App\Models\Mship\State',
    ];

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'email', 'name_first', 'name_last',
    ];

    public static $with = ['feedback', 'qualifications', 'states'];

    public static function authorizable()
    {
        return true;
    }

    /**
     * Global disable of account creation as data comes from core.
     *
     * @param  Request  $request
     * @return bool
     */
    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    /**
     * Global method of disabling the ability to attach resources to Account.
     *
     * @SEMI-TEMPORARY
     *
     * @param  NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model|string  $model
     * @return bool
     */
    public function authorizedToAttachAny(NovaRequest $request, $model)
    {
        return ! in_array(get_class($model), self::$disallowAttach);
    }

    /**
     * Globally disable the ability to delete an account.
     *
     * @param  Request  $request
     * @return bool
     */
    public function authorizedToDelete(Request $request)
    {
        return false;
    }

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
                return $this->qualification_atc->code;
            })->exceptOnForms(),

            Text::make('Pilot Rating(s)', function () {
                return $this->qualifications_pilot_string;
            })->exceptOnForms(),

            BelongsToMany::make('Qualifications')->onlyOnDetail(),

            BelongsToMany::make('States', 'statesHistory')->onlyOnDetail(),

            HasMany::make('Bans', 'bans')->onlyOnDetail(),

            MorphToMany::make('Roles', 'roles', \Vyuldashev\NovaPermission\Role::class),

            MorphToMany::make('Permissions', 'permissions', \Vyuldashev\NovaPermission\Permission::class),

            HasMany::make('Notes')->onlyOnDetail(),

            HasMany::make('Feedback', 'feedback', 'App\Nova\Feedback'),
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
        return [
            new Metrics\TotalAccounts,
            new Metrics\TotalDivisionAccounts,
            new Metrics\TotalNonDivisionAccounts,
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
            new Filters\MembershipState,
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
        return [
            new Actions\Mship\AddNoteToAccount,
            new Actions\Mship\BanAccount,
        ];
    }
}
