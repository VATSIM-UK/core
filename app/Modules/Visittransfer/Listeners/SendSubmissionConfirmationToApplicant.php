<?php namespace App\Modules\Visittransfer\Listeners;

use App\Modules\Visittransfer\Events\ApplicationSubmitted;
use App\Modules\Visittransfer\Jobs\SendApplicantSubmissionConfirmationEmail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSubmissionConfirmationToApplicant implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ApplicationSubmitted $event)
    {
        $confirmationEmailJob = new SendApplicantSubmissionConfirmationEmail($event->application);

        dispatch($confirmationEmailJob->onQueue("low"));
    }
}