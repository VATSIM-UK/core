<?php

namespace App\Jobs\Messages;

use App\Jobs\Job;
use App\Models\Messages\Thread;
use App\Models\Mship\Account\Email;
use App\Models\Messages\Thread\Post;
use Illuminate\Contracts\Mail\Mailer;
use App\Models\Mship\Account as Account;
use App\Models\Messages\Thread\Participant;

class CreateNewMessage extends Job
{
    private $sender = null;
    private $displaySenderAs = null;
    private $recipient = null;
    private $subject = null;
    private $body = null;
    private $systemGenerated = false;
    private $verificationEmail = null;

    // TODO: Find a nice way of overriding the email we're sending to.
    public function __construct(Account $sender, Account $recipient, $subject, $body, $displaySenderAs = null, $isHtml = true, $systemGenerated = false, Account\Email $email = null)
    {
        $this->sender = $sender;
        $this->displaySenderAs = $displaySenderAs;
        $this->recipient = $recipient;
        $this->subject = $subject;
        $this->body = $body;
        $this->systemGenerated = $systemGenerated;
        $this->verificationEmail = $email;
    }

    public function handle(Mailer $mailer)
    {
        // Let's build the thread
        $thread = new Thread();
        $thread->subject = $this->subject;
        $thread->read_only = (bool) $this->systemGenerated;
        $thread->save();

        // Add the participants.
        $displaySenderAs = ($this->displaySenderAs != null ? $this->displaySenderAs : '');
        $thread->participants()->save($this->sender, ['status' => Participant::STATUS_OWNER, 'display_as' => $displaySenderAs, 'read_at' => \Carbon\Carbon::now()]);
        $thread->participants()->save($this->recipient, ['status' => Participant::STATUS_VIEWER]);

        // Now the post.
        $post = new Post();
        $post->content = $this->body;
        $thread->posts()->save($post);
        $this->sender->messagePosts()->save($post);

        dispatch((new SendMessageEmail($post, true, $this->verificationEmail))->onQueue('emails'));
    }
}
