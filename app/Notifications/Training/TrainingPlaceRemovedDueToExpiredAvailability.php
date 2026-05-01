<?php

declare(strict_types=1);

namespace App\Notifications\Training;

use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Notifications\DiscordNotification;
use App\Notifications\DiscordNotificationChannel;
use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * This notification is sent to an account when their training place is removed
 * because an availability warning expired without a subsequent successful availability check.
 */
class TrainingPlaceRemovedDueToExpiredAvailability extends Notification implements DiscordNotification
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
        $channels = ['mail'];

        // Only add Discord if the training team has a registered discord channel id
        if (! empty($this->getChannel())) {
            $channels[] = DiscordNotificationChannel::class;
        }

        return $channels;
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

    public function toDiscord($notifiable)
    {
        $trainingPlace = $this->availabilityWarning->trainingPlace;
        $position = $trainingPlace->trainingPosition->position;

        return [
            'content' => null,
            'embeds' => [
                [
                    'title' => 'Training Place Automatically Removed',
                    'description' => "The training place for **{$notifiable->name} ({$notifiable->id})** on **{$position->name} ({$position->callsign})** has been removed because they failed to resolve a pending availability check.",
                    'color' => 15158332,
                    'fields' => [
                        [
                            'name' => 'Warning Timeline',
                            'value' => '**Issued:** '.$this->availabilityWarning->created_at->format('d/m/Y')."\n**Expired:** ".$this->availabilityWarning->expires_at->format('d/m/Y'),
                            'inline' => false,
                        ],
                    ],
                    'timestamp' => now()->toIso8601String(),
                ],
            ],
        ];
    }

    public function getChannel(): string
    {
        return $this->availabilityWarning->trainingPlace->trainingPosition->training_team_discord_channel_id ?? '';
    }
}
