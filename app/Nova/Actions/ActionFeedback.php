<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Textarea;

class ActionFeedback extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        /** @var \App\Models\Mship\Feedback\Feedback */
        $feedback = $models->first();

        if ($feedback->actioned) {
            return Action::danger('This feedback has already been actioned.');
        }

        $feedback->markActioned(auth()->user(), $fields->comment);

        return Action::message('Feedback actioned!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Textarea::make('Comment')->rules('required', 'min:10'),
        ];
    }
}
