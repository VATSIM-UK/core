<?php

namespace App\Jobs\Messages;

use App\Jobs\Job;
use App\Models\Messages\Thread\Post;
use App\Models\Mship\Account;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNotificationEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $subject;
    private $body;
    private $sender;
    private $senderEmail     = null;
    private $senderDisplayAs = null;
    private $recipient;
    private $recipientEmail  = null;

    public function __construct($subject, $body, Account $recipient, Account $sender, Array $overrides = [])
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->sender = $sender;
        $this->senderDisplayAs = array_get($overrides, 'sender_display_as');
        $this->senderEmail = array_get($overrides, 'sender_email');
        $this->recipient = $recipient;
        $this->recipientEmail = array_get($overrides, 'recipient_email');
    }

    public function handle(Mailer $mailer)
    {
        $sender = $this->sender;
        $recipient = $this->recipient;
        $subject = $this->subject;
        $body = $this->body;

        $senderDisplayAs = $this->senderDisplayAs;
        $senderEmail = $this->senderEmail;
        $recipientEmail = $this->recipientEmail;

        $mailer->send("emails.messages.post", [
            "recipient" => $recipient,
            "sender"    => $sender,
            "body"      => $body,
        ], function ($m) use ($subject, $recipient, $recipientEmail, $sender, $senderEmail, $senderDisplayAs) {
            $m->subject($subject);
            $m->to(($recipientEmail ? $recipientEmail : $recipient->email), $recipient->name);

            $m->from(($senderEmail ? $senderEmail : $sender->email), ($senderDisplayAs ? $senderDisplayAs : $sender->name));
            $m->replyTo(($senderEmail ? $senderEmail : $sender->email), ($senderDisplayAs ? $senderDisplayAs : $sender->name));
        });
    }
}
