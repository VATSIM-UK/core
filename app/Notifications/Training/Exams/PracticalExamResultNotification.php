<?php

namespace App\Notifications\Training\Exams;

use App\Models\Cts\PracticalResult;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PracticalExamResultNotification extends Notification
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
        $result = $this->practicalResult->resultHuman();
        $examType = $this->practicalResult->exam;
        $position = $this->practicalResult->examBooking->position_1 ?? 'N/A';
        $examiner = $this->practicalResult->examBooking->examiners->primaryExaminer->account->name ?? 'N/A';
        $date = $this->practicalResult->date->format('d/m/Y H:i');

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject("Your {$examType} Practical Exam Result")
            ->view('emails.training.exams.practical_exam_result_student', [
                'recipient' => $notifiable,
                'practicalResult' => $this->practicalResult,
                'result' => $result,
                'examType' => $examType,
                'position' => $position,
                'examiner' => $examiner,
                'date' => $date,
            ]);
    }
}
