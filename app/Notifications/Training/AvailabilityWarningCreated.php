<?php

namespace App\Notifications\Training;

use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Models\Training\WaitingList;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * This notification is sent to an account when an availability warning is created for their training place.
 * This is deliberately not queued, as it's sent within the same database transaction as creating the warning.
 */
class AvailabilityWarningCreated extends Notification
{
    use Queueable;

    public function __construct(public AvailabilityWarning $availabilityWarning)
    {
        //
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
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $expiresAt = $this->availabilityWarning->expires_at;
        $trainingPlace = $this->availabilityWarning->trainingPlace;
        $isPilot = $trainingPlace->department === WaitingList::PILOT_DEPARTMENT;

        $daysToExpire = (int) ceil(now()->diffInHours($expiresAt, false) / 24);
        $warningDays = $trainingPlace->availabilityWarningDays();

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('Action Required: Update Your Availability')
            ->view(
                $isPilot
                    ? 'emails.training.availability_warning_pilot'
                    : 'emails.training.availability_warning',
                [
                    'recipient' => $notifiable,
                    'expires_at' => $expiresAt,
                    'days_to_expire' => $daysToExpire.' '.Str::plural('day', $daysToExpire),
                    'availability_window' => $warningDays.' '.Str::plural('day', $warningDays),
                ]
            );
    }
}
