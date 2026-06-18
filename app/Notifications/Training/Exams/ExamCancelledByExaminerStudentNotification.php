<?php

namespace App\Notifications\Training\Exams;

use App\Enums\EmailType;
use App\Models\Cts\ExamBooking;
use App\Models\Mship\Account;
use App\Notifications\Contracts\HasEmailType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExamCancelledByExaminerStudentNotification extends Notification implements HasEmailType
{
    use Queueable;

    public function __construct(
        private ExamBooking $examBooking,
        private Account $cancelledByExaminer,
    ) {}

    public function getEmailType(): EmailType
    {
        return EmailType::ExamCancelled;
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
        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('Your practical exam has been cancelled')
            ->view('emails.training.exams.exam_cancelled_by_examiner_student', [
                'recipient' => $notifiable,
                'examBooking' => $this->examBooking,
                'cancelledByExaminer' => $this->cancelledByExaminer,
            ]);
    }
}
