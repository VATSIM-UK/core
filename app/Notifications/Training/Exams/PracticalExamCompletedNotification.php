<?php

namespace App\Notifications\Training\Exams;

use App\Models\Cts\PracticalResult;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PracticalExamCompletedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private PracticalResult $practicalResult) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject("Practical Exam Completed - {$this->practicalResult->student->account->name} - {$this->practicalResult->exam}")
            ->view('emails.training.exams.practical_exam_completed_staff', [
                'recipientName' => $notifiable->routes["mail"],
                'practicalResult' => $this->practicalResult,
            ]);

    }
}
