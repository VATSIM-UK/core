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
            ID::make()->sortable()->canSeeWhen('elevatedInformation', $this),

            TextWithSlug::make('Name')
                ->rules(['required'])
                ->creationRules('unique:training_waiting_list,name')
                ->slug('slug'),

            Slug::make('Slug')->onlyOnForms()->canSeeWhen('elevatedInformation', $this),

            Select::make('Department')->options([
                'atc' => 'ATC Training',
                'pilot' => 'Pilot Training',
            ])->displayUsingLabels()->rules(['required'])->sortable(),

            new Panel('Notes on Flags', [
                Heading::make('When deleting a flag, the changes will be made to the data but to see them visually,
                you need to refresh the page.')->canSeeWhen('addFlags', $this)
            ]),

            HasMany::make('Flags', 'flags', WaitingListFlag::class)
                ->help('When removing a flag, please refresh the page.')
                ->canSeeWhen('addFlags', $this),

            WaitingListsManager::make(),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->whereIn('department', $request->user()->authorisedDepartments());
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
        return [
            (new AddStudentToWaitingList)->canSee(function (Request $request) {
                return $request->user()->can('addAccounts', $this->model());
            })->canRun(function (Request $request) {
                return $request->user()->can('addAccounts', $this->model());
            }),

            (new AddFlagToWaitingList)->canSee(function (Request $request) {
                return $request->user()->can('addFlags', $this->model());
            })->canRun(function (Request $request) {
                return $request->user()->can('addFlags', $this->model());
            })
        ];
    }
}
