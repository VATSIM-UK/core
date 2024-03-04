<?php

namespace App\Notifications\Mship;

use App\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection;

class FeedbackSummary extends Notification implements ShouldQueue
{
    use Queueable;

    protected $feedback;

    protected $feedbackSince;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Carbon $feedbackSince, Collection $feedback)
    {
        parent::__construct();

        $this->feedbackSince = $feedbackSince;
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
        $subject = 'Feedback Summary';

        return (new MailMessage)
            ->from('community@vatsim.uk', 'VATSIM UK - Community Department')
            ->subject($subject)
            ->view('emails.mship.feedback.summary', [
                'feedbackSince' => $this->feedbackSince,
                'feedback' => $this->feedback,
                'recipient' => $notifiable,
                'subject' => $subject,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return ['feedback_ids' => $this->feedback->pluck('id')];
    }
}
