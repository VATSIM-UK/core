<?php

namespace App\Modules\Visittransfer\Jobs;

use View;
use App\Jobs\Job;
use App\Models\Mship\Account;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\Messages\SendNotificationEmail;
use App\Modules\Visittransfer\Models\Reference;
use App\Modules\Visittransfer\Models\Application;

class SendApplicantReferenceSubmissionEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $application = null;
    private $reference   = null;

    public function __construct(Reference $reference)
    {
        $this->reference   = $reference;
        $this->application = $reference->application;
    }

    /**
     * Send the user an email confirming that a referee has provided a reference.
     *
     * @return void
     */
    public function handle()
    {
        $displayFrom = 'VATSIM UK - Community Department';

        $subject = '['.$this->application->public_id."] Reference from '".$this->reference->account->name."' Submitted";

        $body = View::make('visittransfer::emails.applicant.reference_submitted')
                    ->with('reference', $this->reference)
                    ->with('application', $this->application)
                    ->render();

        $sender = Account::find(VATUK_ACCOUNT_SYSTEM);

        $createNewMessage = new SendNotificationEmail($subject, $body, $this->application->account, $sender, [
            'sender_display_as' => $displayFrom,
            'sender_email'      => 'community@vatsim-uk.co.uk',
        ]);

        dispatch($createNewMessage->onQueue('emails'));
    }
}
