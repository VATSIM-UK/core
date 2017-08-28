<?php

namespace App\Notifications\Mship;

use App\Models\Messages\Thread\Post;
use App\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class MemberEmail extends Notification implements ShouldQueue
{
    use Queueable;

    protected $post;
    protected $allowReply;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Post $post, $allowReply)
    {
        parent::__construct();

        $this->post = $post;
        $this->allowReply = $allowReply;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $author = $this->post->author;

        $subject = "Message from {$author->name}: {$this->post->thread->subject}";

        $message = (new MailMessage)
            ->from('no-reply@vatsim.uk', 'VATSIM UK')
            ->subject($subject)
            ->view('emails.mship.member_email', [
                'replyAllowed' => $this->allowReply,
                'sender' => $author,
                'recipient' => $notifiable,
                'subject' => $subject,
                'messageContent' => $this->post->content,
            ]);

        if ($this->allowReply) {
            $message->replyTo($author->email, $author->name);
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [];
    }
}
