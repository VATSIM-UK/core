<?php

namespace App\Nova;

use App\Nova\Actions\Training\AddFlagToWaitingList;
use App\Nova\Actions\Training\AddStudentToWaitingList;
use Benjaminhirsch\NovaSlugField\Slug;
use Benjaminhirsch\NovaSlugField\TextWithSlug;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
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
     * @param  \Illuminate\Http\Request  $request
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

            HasMany::make('Flags', 'flags', WaitingListFlag::class)
                ->canSeeWhen('addFlags', $this),

            WaitingListsManager::make(),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->whereIn('department', $request->user('web')->authorisedDepartments());
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
        $model = optional($request->findModelQuery()->first());

        return [
            (new AddStudentToWaitingList)->canSee(function (Request $request) use ($model) {
                return $request->user()->can('use-permission', "waitingLists/{$model->department}/addAccounts");
            })->canRun(function (Request $request) use ($model) {
                return $request->user()->can('use-permission', "waitingLists/{$model->department}/addAccounts");
            })->onlyOnDetail(),

            (new AddFlagToWaitingList)->canSee(function (Request $request) use ($model) {
                return $request->user()->can('use-permission', "waitingLists/{$model->department}/addFlags");
            })->canRun(function (Request $request) use ($model) {
                return $request->user()->can('use-permission', "waitingLists/{$model->department}/addFlags");
            })->onlyOnDetail()
        ];
    }
}
