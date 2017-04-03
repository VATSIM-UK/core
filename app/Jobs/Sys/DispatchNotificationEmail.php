<?php

namespace App\Jobs\Sys;

use App\Jobs\Job;
use App\Models\Mship\Account;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class DispatchNotificationEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $senderEmail     = null;
    private $displaySenderAs = null;
    private $recipient       = null;
    private $recipientEmail  = null;
    private $subject         = null;
    private $body            = null;

    // TODO: Not happy with this many params.  Fix it, please.
    public function __construct($senderName, $senderEmail, Account $recipient, Account\Email $recipientEmail = null, $subject, $body)
    {
        $this->$senderEmail    = $senderEmail;
        $this->displaySenderAs = $senderName;
        $this->recipient       = $recipient;
        $this->recipientEmail  = $recipientEmail;
        $this->subject         = $subject;
        $this->body            = $body;
    }

    public function handle(Mailer $mailer)
    {
        $mailer->send('emails.messages.post', ['recipient' => $this->recipient, 'body' => $this->body, 'subject' => $this->subject], function ($m) {
            $m->subject($this->subject);
            $m->to($this->recipientEmail->email, $this->recipient->name);
        });
    }
}
