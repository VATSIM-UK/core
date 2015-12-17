<?php

namespace App\Jobs\Messages;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Bus\SelfHandling;
use \App\Models\Mship\Account as Account;

class CreateNewMessage extends \App\Jobs\Job implements SelfHandling
{
    private $sender = null;
    private $displaySenderAs = null;
    private $recipient = null;
    private $subject = null;
    private $body = null;
    private $systemGenerated = false;

    public function __construct(Account $sender, Account $recipient, $subject, $body, $displaySenderAs=null, $isHtml=true, $systemGenerated=false)
    {
        $this->sender = $sender;
        $this->displaySenderAs = $displaySenderAs;
        $this->recipient = $recipient;
        $this->subject = $subject;
        $this->body = $body;
        $this->systemGenerated = $systemGenerated;
    }

    public function handle(Mailer $mailer)
    {
        // Let's build the thread
        $thread = new \App\Models\Messages\Thread();
        $thread->subject = $this->subject;
        $thread->read_only = (boolean) $this->systemGenerated;
        $thread->save();

        // Add the participants.
        $displaySenderAs = ($this->displaySenderAs != null ? $this->displaySenderAs : "");
        $thread->participants()->save($this->sender, ["status" => \App\Models\Messages\Thread\Participant::STATUS_OWNER, "display_as" => $displaySenderAs, "read_at" => \Carbon\Carbon::now()]);
        $thread->participants()->save($this->recipient, ["status" => \App\Models\Messages\Thread\Participant::STATUS_VIEWER]);

        // Now the post.
        $post = new \App\Models\Messages\Thread\Post();
        $post->content = $this->body;
        $thread->posts()->save($post);
        $this->sender->messagePosts()->save($post);

        \Bus::dispatch(new \App\Jobs\Messages\SendMessageEmail($post));
    }
}
