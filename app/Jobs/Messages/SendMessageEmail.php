<?php

namespace App\Jobs\Messages;

use App\Jobs\Job;
use App\Models\Messages\Thread\Post;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendMessageEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $post;
    private $isNew = true;

    public function __construct(Post $post, $isNew = true)
    {
        $this->post = $post;
        $this->isNew = (boolean) $isNew;
    }

    public function handle(Mailer $mailer)
    {
        $post = $this->post;
        $isNew = $this->isNew;

        // Let's get all participants of the post.
        foreach ($post->thread->participants as $participant) {
            if ($post->account_id == $participant->id) {
                continue;
            } // We won't send to the writer!

            $mailer->send("emails.messages.post", ["recipient" => $participant, "sender" => $post->author, "body" => $this->post->content], function ($m) use ($participant, $post, $isNew) {
                $m->subject(($isNew ? $post->thread->subject : "RE: ".$post->thread->subject));
                $m->to($participant->email, $participant->name);

                // Send this one to all the secondary emails.
                // @disabled 2.2.0 Awaiting improvement in 2.2.2
                /*foreach($participant->secondary_email_verified as $sev){
                    $m->cc($sev->email, $participant->name. " (Secondary Email)");
                }*/
            });
        }
    }
}
