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

class SendApplicantReferenceRejectionEmail extends Job implements ShouldQueue
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
     * Send the user an email detailing their rejection.
     *
     * @return void
     */
    public function handle()
    {
        $displayFrom = 'VATSIM UK - Community Department';

        $subject = '['.$this->application->public_id."] Reference from '".$this->reference->account->name."' Rejected";

        $body = View::make('visittransfer::emails.applicant.reference_rejected')
                    ->with('reference', $this->reference)
                    ->with('application', $this->application)
                    ->render();

        $sender = Account::find(VATUK_ACCOUNT_SYSTEM);

        $createNewMessage = new SendNotificationEmail($subject, $body, $this->application->account, $sender, [
            'sender_display_as' => $displayFrom,
            'sender_email'      => 'community@vatsim-uk.co.uk',
            'recipient_email'   => $this->reference->email,
        ]);

        dispatch($createNewMessage->onQueue('emails'));
    }
}
