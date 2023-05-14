<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Text;
use Sixlive\TextCopy\TextCopy;

class FeedbackResponse extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Mship\Feedback\Answer';

    /**
     * Set how many related models (Questions) to display on detail.
     *
     * @var int
     */
    public static $perPageViaRelationship = 10;

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
    public static $search = [];

    /**
     * Order responses as they appear on the form.
     *
     * @return mixed
     */
    protected static function applyOrderings($query, array $orderings)
    {
        return $query->orderBy('id');
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    public function authorizedToView(Request $request)
    {
        return false;
    }

    /**
     * Remove from navigation as only a background resource.
     *
     * @return bool
     */
    public static function availableForNavigation(Request $request)
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
            BelongsTo::make('Feedback', 'feedback', 'App\Nova\Feedback'),

            Text::make('Question', function () {
                return $this->question->question;
            }),

            TextCopy::make('Response', 'response')->copyButtonTitle('Copy Value'),
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
