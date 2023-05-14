<?php

namespace App\Listeners\Mship\Feedback;

use App\Events\Mship\Feedback\NewFeedbackEvent;
use App\Notifications\Mship\FeedbackReceived;

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
