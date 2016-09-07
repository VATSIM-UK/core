<?php namespace App\Modules\Visittransfer\Jobs;

use App\Jobs\Job;
use App\Jobs\Messages\CreateNewMessage;
use App\Jobs\Messages\SendNotificationEmail;
use App\Models\Mship\Account;
use App\Modules\Visittransfer\Models\Application;
use App\Modules\Visittransfer\Models\Reference;
use Bus;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use View;

class SendRefereeNoLongerRequiredEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $application = null;
    private $reference = null;

    public function __construct(Reference $reference)
    {
        $this->reference = $reference;
        $this->application = $reference->application;
    }

    /**
     * Send a referee an email to say thanks!
     *
     * @return void
     */
    public function handle()
    {
        $displayFrom = "VATSIM UK - Community Department";

        $subject = "[".$this->application->public_id."] Reference No Longer Required";

        $body = View::make("visittransfer::emails.reference.reference_not_required")
                    ->with("reference", $this->reference)
                    ->with("application", $this->application)
                    ->render();


        $sender = Account::find(VATUK_ACCOUNT_SYSTEM);

        $createNewMessage = new SendNotificationEmail($subject, $body, $this->reference->account, $sender, [
            "sender_display_as" => $displayFrom,
            "sender_email" => "community@vatsim-uk.co.uk",
            "recipient_email" => $this->reference->email,
        ]);

        dispatch($createNewMessage->onQueue("emails"));
    }
}
