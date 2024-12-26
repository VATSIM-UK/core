<?php

namespace App\Listeners\Mship;

use App\Events\Mship\Feedback\NewFeedbackEvent;
use App\Events\Mship\Qualifications\QualificationAdded;
use App\Notifications\Mship\S1TrainingOpportunities;

class SendS1Email
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
    public function handle(QualificationAdded $event)
    {
        $account = $event->account;

        $isS1Qual = $event->qualification->code === 'S1';
        $isDivisionMember = $account->hasState('DIVISION');
        $noTempStates = $account->states->where('type', 'temp')->count() === 0;
        if ($isS1Qual && $isDivisionMember && $noTempStates) {
            $account->notify(new S1TrainingOpportunities);
        }
    }
}
