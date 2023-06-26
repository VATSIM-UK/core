<?php

namespace App\Nova;

use App\Nova\Actions\Training\AddFlagToWaitingList;
use App\Nova\Actions\Training\AddStudentToWaitingList;
use App\Nova\Actions\Training\AddStudentToWaitingListAdmin;
use Benjaminhirsch\NovaSlugField\Slug;
use Benjaminhirsch\NovaSlugField\TextWithSlug;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use Vatsimuk\WaitingListsManager\WaitingListsManager;

class WaitingList extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\\Models\\Training\\WaitingList';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    public static $group = 'Training';

    public static function label()
    {
        return 'Waiting Lists';
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
    ];

    public static $with = ['accounts', 'flags'];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable()->onlyOnForms(),

            TextWithSlug::make('Name')
                ->rules(['required'])
                ->creationRules('unique:training_waiting_list,name')
                ->slug('slug'),

            Slug::make('Slug')
                ->hideFromDetail()
                ->hideFromIndex()
                ->hideWhenUpdating()
                ->help('This is generated automatically and does not need to be amended.')
                ->required(),

            Select::make('Department')->options([
                'atc' => 'ATC Training',
                'pilot' => 'Pilot Training',
            ])->displayUsingLabels()->rules(['required'])->sortable(),

            Select::make('Flags Check')->options([
                'all' => 'ALL Flags',
                'any' => 'ANY Flags',
            ])->displayUsingLabels()->rules(['required'])
                ->help('Waiting lists can be set so either a: all flags need to be met or b: any of the flags'),

            HasMany::make('Flags', 'flags', WaitingListFlag::class)
                ->canSeeWhen('addFlags', $this),

            WaitingListsManager::make()->withMeta(['type' => $this->department]),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->whereIn('department', $request->user('web')->waitingListDepartments());
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
            (new AddStudentToWaitingList)
                ->onlyOnDetail()
                ->canSee(function (Request $request) {
                    return $request->user()->can('use-permission', 'waitingLists/addAccounts');
                })->canRun(function (Request $request) {
                    return $request->user()->can('use-permission', 'waitingLists/addAccounts');
                }),

            (new AddFlagToWaitingList)
                ->onlyOnDetail()
                ->canSee(function (Request $request) {
                    return $request->user()->can('use-permission', 'waitingLists/addFlags');
                })->canRun(function (Request $request) {
                    return $request->user()->can('use-permission', 'waitingLists/addFlags');
                }),

            (new AddStudentToWaitingListAdmin())
                ->onlyOnDetail()
                ->canSee(function (Request $request) {
                    return $request->user()->can('use-permission', 'waitingLists/addAccountsAdmin');
                })->canRun(function (Request $request) {
                    return $request->user()->can('use-permission', 'waitingLists/addAccountsAdmin');
                }),
        ];
    }
}
