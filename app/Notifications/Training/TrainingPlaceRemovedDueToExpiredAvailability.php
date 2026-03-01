<?php

declare(strict_types=1);

namespace App\Notifications\Training;

use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * This notification is sent to an account when their training place is removed
 * because an availability warning expired without a subsequent successful availability check.
 */
class TrainingPlaceRemovedDueToExpiredAvailability extends Notification
{
    public function __construct(public AvailabilityWarning $availabilityWarning) {}

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $trainingPlace = $this->availabilityWarning->trainingPlace;
        $removalDate = now()->format('d M Y');

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('Attention: Your Training Place Has Been Removed - Availability Check Expired')
            ->view('emails.training.training_place_removed_expired_availability', [
                'recipient' => $notifiable,
                'training_place_position_name' => $trainingPlace->trainingPosition?->position?->name ?? 'N/A',
                'removal_date' => $removalDate,
            ]);
    }
}
