<?php

namespace App\Nova\Actions;

use App\Nova\Utilities\FeedbackActionFields;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;

class SendFeedback extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        /** @var \App\Models\Mship\Feedback\Feedback */
        $feedback = $models->first();
        $actioner = auth()->user();

        if ($feedback->actioned_at != null) {
            return Action::danger('This feedback is already sent to the user.');
        }

        $feedback->markSent($actioner, $fields->comment);

        return Action::message('Feedback sent!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Text::make('Comment')->rules('required', 'min:3'),
        ];
    }
}
