<?php

namespace App\Listeners\Mship\Feedback;

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
     * @param  NewFeedbackEvent $event
     * @return void
     */
    public function handle(NewFeedbackEvent $event)
    {
        $contact = $event->feedback->form->contact;
        if ($contact) {
            $contact->notify(new FeedbackReceived($event->feedback));
        }
    }
}
