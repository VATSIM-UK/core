<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class Feedback extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Mship\Feedback\Feedback';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'account' => ['id', 'name_first', 'name_last'],
    ];

    public static $with = ['answers', 'account', 'submitter'];

    /**
     * The group assigned to this resource.
     *
     * @var string
     */
    public static $group = 'Feedback';

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public static function availableForNavigation(Request $request)
    {
        return $request->user()->can('use-permission', 'feedback');
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->whereNotIn('account_id', $request->user()->hiddenFeedbackUsers());
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
            ID::make()->sortable()->hideFromDetail(),

            Text::make('Feedback Form', function () {
                return $this->form->name;
            }),

            BelongsTo::make('Subject', 'account', 'App\Nova\Account'),

            BelongsTo::make('Submitted By', 'submitter', 'App\Nova\Account')
                ->canSeeWhen('seeSubmitter', $this),

            DateTime::make('Submitted At', 'created_at')->format('Do MMMM YYYY HH:mm'),

            new Panel('Actioned Information', [
                Boolean::make('Actioned', function () {
                    return $this->actioned_at != null;
                }),
                DateTime::make('Actioned At')->canSee(function () {
                    return $this->actioned_at != null;
                })->onlyOnDetail()->format('Do MMMM YYYY HH:mm'),
                BelongsTo::make('Actioned By', 'actioner', 'App\Nova\Account')->canSee(function () {
                    return $this->actioned_at != null;
                })->onlyOnDetail(),
                Textarea::make('Comment', 'actioned_comment')->canSee(function () {
                    return $this->actioned_at != null;
                })->onlyOnDetail(),
            ]),

            new Panel('Sent Information', [
                Boolean::make('Sent To User', function () {
                    return $this->sent_at != null;
                }),
                DateTime::make('Sent At')->canSee(function () {
                    return $this->sent_at != null;
                })->onlyOnDetail()->format('Do MMMM YYYY HH:mm'),
                BelongsTo::make('Sent By', 'actioner', 'App\Nova\Account')->canSee(function () {
                    return $this->sent_at != null;
                })->onlyOnDetail(),
                Textarea::make('Comment', 'sent_comment')->canSee(function () {
                    return $this->sent_at != null;
                })->onlyOnDetail(),
            ]),

            HasMany::make('Answers', 'answers', 'App\Nova\FeedbackResponse'),
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
            new Metrics\TotalFeedback,
            new Metrics\TotalFeedbackGraph,
            new Metrics\ActionedUnactionedFeedback,
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
            new Filters\FeedbackForm,
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
            (new Actions\ActionFeedback)->onlyOnDetail()
                ->canSeeWhen('actionFeedback', $this)
                ->canRun(function () {
                    return true;
                }),
            (new Actions\SendFeedback)->onlyOnDetail()
                ->canSeeWhen('actionFeedback', $this)
                ->canRun(function () {
                    return true;
                }),
        ];
    }
}
