<?php

namespace App\Modules\Visittransfer\Jobs;

use View;
use App\Jobs\Job;
use App\Models\Mship\Account;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\Messages\SendNotificationEmail;
use App\Modules\Visittransfer\Models\Application;

class SendApplicantStatusChangeEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $application = null;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Send the user an email to advise them that their application's status has changed.
     *
     * @return void
     */
    public function handle()
    {
        $displayFrom = 'VATSIM UK - Community Department';

        $subject = '['.$this->application->public_id.'] '.$this->application->type_string.' Application '.$this->application->status_string;

        $body = View::make('visittransfer::emails.applicant.status_changed')
                    ->with('application', $this->application)
                    ->render();

        $sender = Account::find(VATUK_ACCOUNT_SYSTEM);

        $createNewMessage = new SendNotificationEmail($subject, $body, $this->application->account, $sender, [
            'sender_display_as' => $displayFrom,
            'sender_email' => 'community@vatsim-uk.co.uk',
        ]);

        dispatch($createNewMessage->onQueue('emails'));
    }
}
