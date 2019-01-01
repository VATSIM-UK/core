<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
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

    public static $with =  ['answers', 'account', 'submitter'];

    /**
     * The group assigned to this resource.
     *
     * @var string
     */
    public static $group = 'Feedback';

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

            BelongsTo::make('Subject', 'account', 'App\Nova\Account'),

            BelongsTo::make('Submitted By', 'submitter', 'App\Nova\Account'),

            Text::make('Feedback Form', function () {
                return $this->form->name;
            }),

            new Panel('Actioned Information', [
                Boolean::make('Actioned' , function() {
                    return $this->actioned_at != null;
                }),
                DateTime::make('Actioned At')->canSee(function () {
                    return $this->actioned_at != null;
                }),
                BelongsTo::make('Actioned By', 'actioner', 'App\Nova\Account')->canSee(function () {
                    return $this->actioned_at != null;
                }),
                Text::make('Comment', 'actioned_comment')->canSee(function () {
                    return $this->actioned_at != null;
                }),
            ]),

            new Panel('Sent Information', [
                Boolean::make('Sent To User', function () {
                    return $this->sent_at != null;
                }),
                DateTime::make('Sent At')->canSee(function () {
                    return $this->sent_at != null;
                }),
                BelongsTo::make('Sent By', 'actioner', 'App\Nova\Account')->canSee(function () {
                    return $this->sent_at != null;
                }),
                Text::make('Comment', 'sent_comment')->canSee(function () {
                    return $this->sent_at != null;
                }),
            ]),

            HasMany::make('Answers', 'answers', 'App\Nova\FeedbackResponse')->withMeta(['perPage' => 20])
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
        return [
            new Filters\FeedbackForm
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
            (new Actions\ActionFeedback)->onlyOnDetail(),
            (new Actions\SendFeedback)->onlyOnDetail(),
        ];
    }
}
