<?php

namespace App\Notifications\Mship;

use Illuminate\Bus\Queueable;
use App\Notifications\Notification;
use App\Models\Mship\Feedback\Feedback;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class FeedbackReceived extends Notification implements ShouldQueue
{
    use Queueable;

    private $feedback;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Feedback $feedback)
    {
        parent::__construct();

        $this->feedback = $feedback;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $subject = 'New Member Feedback Received';

        return (new MailMessage)
            ->from('community@vatsim.uk', 'VATSIM UK - Community Department')
            ->subject($subject)
            ->view('emails.mship.feedback.new_feedback', ['feedback' => $this->feedback, 'recipient' => $notifiable, 'subject' => $subject]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return ['feedback_id' => $this->feedback->id];
    }
}
