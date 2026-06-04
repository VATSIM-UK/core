<?php

namespace App\Notifications\Training\Exams;

use App\Enums\EmailType;
use App\Models\Cts\ExamBooking;
use App\Models\Mship\Account;
use App\Notifications\Contracts\HasEmailType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExamSessionCancelledForCoExaminerNotification extends Notification implements HasEmailType
{
    use Queueable;

    public function __construct(
        private ExamBooking $examBooking,
        private Account $cancelledByExaminer,
    ) {}

    public function getEmailType(): EmailType
    {
        return EmailType::ExaminerExamCancelled;
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->examBooking->loadMissing('student');

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject("{$this->examBooking->exam} Practical Exam Session Cancelled")
            ->view('emails.training.exams.exam_session_cancelled_for_co_examiner', [
                'recipient' => $notifiable,
                'examBooking' => $this->examBooking,
                'cancelledByExaminer' => $this->cancelledByExaminer,
            ]);
    }
}
