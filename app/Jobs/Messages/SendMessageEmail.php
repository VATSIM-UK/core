<?php

namespace App\Jobs\Messages;

use App\Jobs\Job;
use App\Models\Messages\Thread\Post;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Mship\Account\Email as Email;

class SendMessageEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $post;
    private $isNew             = true;
    private $verificationEmail = null;

    public function __construct(Post $post, $isNew = true, Email $verificationEmail = null)
    {
        $this->post              = $post;
        $this->isNew             = (bool) $isNew;
        $this->verificationEmail = $verificationEmail;
    }

    public function handle(Mailer $mailer)
    {
        $post  = $this->post;
        $isNew = $this->isNew;

        // Let's get all participants of the post.
        foreach ($post->thread->participants as $participant) {
            if ($post->account_id == $participant->id) {
                continue;
            } // We won't send to the writer!

            $recipientAddress = $participant->email;

            // Check if there is a verification email address to be used instead of account primary
            if ($this->verificationEmail != null) {
                // Use the newly added verification email address instead
                $recipientAddress = $this->verificationEmail->email;
            }

            $mailer->send('emails.messages.post', ['recipient' => $participant, 'sender' => $post->author, 'body' => $this->post->content], function ($m) use ($participant, $post, $isNew, $recipientAddress) {
                $m->subject(($isNew ? $post->thread->subject : 'RE: '.$post->thread->subject));
                $m->to($recipientAddress, $participant->name);

                // Send this one to all the secondary emails.
                // @disabled 2.2.0 Awaiting improvement in 2.2.2
                /*foreach($participant->secondary_email_verified as $sev){
                    $m->cc($sev->email, $participant->name. " (Secondary Email)");
                }*/
            });
        }
    }
}
