<?php namespace App\Modules\Visittransfer\Jobs;

use App\Jobs\Job;
use App\Modules\Visittransfer\Models\Application;
use Bus;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendApplicantSubmissionConfirmationEmail extends Job implements ShouldQueue {
    use InteractsWithQueue, SerializesModels;

    private $application = null;

    public function __construct(Application $application){
        $this->application = $application;
    }

    /**
     * Send the user an email confirming that their application has been submitted and we'll update them on the progress.
     *
     * @return void
     */
    public function handle(){
        $displayFrom = "VATSIM UK - Community Department";
        $subject = "[".$this->application->public_id."] " . $this->application->type_string . " Submitted";
        $body = View::make("visittransfer::XXXXXXXXXXXXXXX")
                    ->with("account", $this->recipient)
                    ->with("token", $this->token)
                    ->render();


        $sender = Account::find(VATUK_ACCOUNT_SYSTEM);
        $isHtml = true;
        $systemGenerated = true;
        $createNewMessage = new CreateNewMessage($sender, $this->recipient, $subject, $body, $displayFrom, $isHtml, $systemGenerated);
        dispatch($createNewMessage->onQueue("emails"));
    }
}
