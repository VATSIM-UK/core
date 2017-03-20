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

class SendTrainingTeamNewAcceptedApplicationEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $application = null;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Send the training department an email to advise of a reference that requires completion.
     *
     * @return void
     */
    public function handle()
    {
        $displayFrom = 'VATSIM UK - Community Department';

        $subject = '['.$this->application->public_id.'] New '.$this->application->facility->name.' '.$this->application->type_string.' Applicant';

        $body = View::make('visittransfer::emails.training.accepted_application')
                    ->with('application', $this->application)
                    ->render();

        $sender = Account::find(VATUK_ACCOUNT_SYSTEM);

        $recipient     = $this->application->facility->training_team.'-team@vatsim-uk.co.uk';
        $recipientName = strtoupper($this->application->facility->training_team).' Training Team';

        // TODO: Use the staff services feature to get all community members.
        $createNewMessage = new SendNotificationEmail($subject, $body, Account::find(VATUK_ACCOUNT_SYSTEM), $sender, [
            'sender_display_as' => $displayFrom,
            'sender_email'      => 'community@vatsim-uk.co.uk',
            'recipient_email'   => $recipient,
            'recipient_name'    => $recipientName,
        ]);

        dispatch($createNewMessage->onQueue('emails'));
    }
}
