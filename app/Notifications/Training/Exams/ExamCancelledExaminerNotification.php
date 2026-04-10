<?php

namespace App\Notifications\Training\Exams;

use App\Models\Cts\ExamBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExamCancelledExaminerNotification extends Notification
{
    use Queueable;

    public function __construct(
        private ExamBooking $examBooking,
        private string $reason,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $examBooking = $this->examBooking->load(['student']);

        $studentName = $examBooking->student?->account?->name ?? 'Unknown';
        $studentCid = $examBooking->student?->account?->id ?? 'Unknown';

        $position = collect([$examBooking->position_1, $examBooking->position_2])
            ->filter()
            ->implode(' / ');

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject("{$examBooking->exam} Practical Exam Cancelled")
            ->view('emails.training.exams.exam_cancelled_examiner', [
                'recipient' => $notifiable,
                'examBooking' => $examBooking,
                'examType' => $examBooking->exam,
                'position' => $position,
                'takenDate' => $examBooking->taken_date,
                'takenFrom' => $examBooking->taken_from,
                'takenTo' => $examBooking->taken_to,
                'studentName' => $studentName,
                'studentCid' => $studentCid,
                'reason' => $this->reason,
            ]);
    }
}
