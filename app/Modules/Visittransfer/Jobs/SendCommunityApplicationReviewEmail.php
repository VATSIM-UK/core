<?php

namespace App\Modules\Visittransfer\Jobs;

use App\Jobs\Job;
use App\Jobs\Messages\SendNotificationEmail;
use App\Models\Mship\Account;
use App\Modules\Visittransfer\Models\Application;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use View;

class SendCommunityApplicationReviewEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $application = null;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Send the community department an email to advise of a reference that requires completion.
     *
     * @return void
     */
    public function handle()
    {
        $displayFrom = 'VATSIM UK - Community Department';

        $subject = '['.$this->application->public_id.'] New '.$this->application->type_status.' Application';

        $body = View::make('visittransfer::emails.community.new_application')
                    ->with('application', $this->application)
                    ->render();


        $sender = Account::find(VATUK_ACCOUNT_SYSTEM);
        $recipient = Account::find(1002707);

        // TODO: Use the staff services feature to get all community members.
        $createNewMessage = new SendNotificationEmail($subject, $body, $recipient, $sender, [
            'sender_display_as' => $displayFrom,
            'sender_email' => 'community@vatsim-uk.co.uk',
        ]);

        dispatch($createNewMessage->onQueue('emails'));
    }
}
