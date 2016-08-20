<?php namespace App\Modules\Visittransfer\Jobs;

use App\Jobs\Job;
use App\Jobs\Messages\CreateNewMessage;
use App\Models\Mship\Account;
use App\Modules\Visittransfer\Models\Application;
use App\Modules\Visittransfer\Models\Reference;
use Bus;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use View;

class SendRefereeConfirmationEmail extends Job implements ShouldQueue {
    use InteractsWithQueue, SerializesModels;

    private $application = null;
    private $reference = null;

    public function __construct(Reference $reference){
        $this->reference = $reference;
        $this->application = $reference->application;
    }

    /**
     * Send a referee an email to say thanks!
     *
     * @return void
     */
    public function handle(){
        $displayFrom = "VATSIM UK - Community Department";

        $subject = "[".$this->application->public_id."] Thank you for your reference.";

        $body = View::make("visittransfer::emails.reference.reference_submitted")
                    ->with("reference", $this->reference)
                    ->with("application", $this->application)
                    ->render();


        $sender = Account::find(VATUK_ACCOUNT_SYSTEM);
        $isHtml = true;
        $systemGenerated = true;
        $createNewMessage = new CreateNewMessage($sender, $this->reference->account, $subject, $body, $displayFrom, $isHtml, $systemGenerated);

        dispatch($createNewMessage->onQueue("emails"));
    }
}
