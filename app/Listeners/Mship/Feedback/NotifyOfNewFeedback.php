<?php

namespace App\Listeners\Mship\Feedback;

use App\Models\Contact;
use App\Notifications\Mship\FeedbackReceived;
use App\Events\Mship\Feedback\NewFeedbackEvent;

class NotifyOfNewFeedback
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  NewFeedbackEvent  $event
     * @return void
     */
    public function handle(NewFeedbackEvent $event)
    {
        $feedback = $event->feedback;

        if ($feedback->isATC()) {
            $recipient = Contact::where('key', 'ATC_TRAINING')->first();
        } elseif ($feedback->isPilot()) {
            $recipient = Contact::where('key', 'PILOT_TRAINING')->first();
        } else {
            return;
        }

        $recipient->notify(new FeedbackReceived($feedback));
    }
}
