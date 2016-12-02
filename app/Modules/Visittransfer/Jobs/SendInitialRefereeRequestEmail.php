<?php

namespace App\Modules\Visittransfer\Jobs;

use App\Jobs\Job;
use App\Jobs\Messages\SendNotificationEmail;
use App\Models\Mship\Account;
use App\Modules\Visittransfer\Models\Application;
use App\Modules\Visittransfer\Models\Reference;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use View;

class SendInitialRefereeRequestEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $reference = null;
    private $application = null;

    public function __construct(Reference $reference)
    {
        $this->reference = $reference;
        $this->application = $this->reference->application;
    }

    /**
     * Send the referee an email requesting a reference for this applicant.
     *
     * @return void
     */
    public function handle()
    {
        $displayFrom = 'VATSIM UK - Community Department';
        $subject = '['.$this->application->public_id.'] '.$this->application->type_string.' Reference Request';

        $body = View::make('visittransfer::emails.reference.request')
                    ->with('reference', $this->reference)
                    ->with('application', $this->reference->application)
                    ->with('token', $this->reference->token)
                    ->render();


        $sender = Account::find(VATUK_ACCOUNT_SYSTEM);

        $createNewMessage = new SendNotificationEmail($subject, $body, $this->reference->account, $sender, [
            'sender_display_as' => $displayFrom,
            'sender_email' => 'community@vatsim-uk.co.uk',
            'recipient_email' => $this->reference->email,
        ]);

        dispatch($createNewMessage->onQueue('emails'));

        $this->reference->status = Reference::STATUS_REQUESTED;
        $this->reference->contacted_at = \Carbon\Carbon::now();
        $this->reference->save();
    }
}
