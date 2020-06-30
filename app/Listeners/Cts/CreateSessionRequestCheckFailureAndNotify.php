<?php

namespace App\Listeners\Cts;

use App\Events\Cts\StudentFailedSessionRequestCheck;
use App\Models\Training\SessionRequestCheck;
use App\Notifications\Training\FirstSessionCheckWarning;
use App\Notifications\Training\SecondSessionCheckWarning;

class CreateSessionRequestCheckFailureAndNotify
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
     * @param  object  $event
     * @return void
     */
    public function handle(StudentFailedSessionRequestCheck $event)
    {
        $check = SessionRequestCheck::firstOrCreate(['rts_id' => $event->rtsId, 'account_id' => $event->account->id]);

        switch ($check->stage) {
            case SessionRequestCheck::NO_WARNING_SENT:
                $check->account->notify(new FirstSessionCheckWarning);
                $check->incrementStage();
                break;
            case SessionRequestCheck::FIRST_WARNING_SENT:
                $check->account->notify(new SecondSessionCheckWarning);
                $check->incrementStage();
                break;
            case SessionRequestCheck::SECOND_WARNING_SENT:
                // send notification to TD via helpdesk
                // incrementStage to 3
                // soft delete
                break;
        }
    }
}
