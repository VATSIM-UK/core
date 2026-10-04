<?php

declare(strict_types=1);

namespace App\Notifications\Training;

use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Models\Training\WaitingList;
use App\Notifications\DiscordNotification;
use App\Notifications\DiscordNotificationChannel;
use App\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * This notification is sent to an account when their training place is removed
 * because they have failed the availability check on four occasions (having
 * previously resolved three failed checks within the warning window).
 */
class TrainingPlaceRemovedDueToFourthAvailabilityFailure extends Notification implements DiscordNotification
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
        $isPilot = $trainingPlace->department === WaitingList::PILOT_DEPARTMENT;

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('Attention: Your Training Place Has Been Removed - Repeated Availability Check Failures')
            ->view(
                $isPilot
                    ? 'emails.training.training_place_removed_fourth_availability_failure_pilot'
                    : 'emails.training.training_place_removed_fourth_availability_failure',
                [
                    'recipient' => $notifiable,
                    'training_place_position_name' => $trainingPlace->trainingPosition?->position?->name ?? $trainingPlace->display_name,
                    'removal_date' => $removalDate,
                ]
            );
    }

    public function toDiscord($notifiable)
    {
        $trainingPlace = $this->availabilityWarning->trainingPlace;
        $positionLabel = $this->placeLabel($trainingPlace);

        $warningDates = $trainingPlace->availabilityWarnings()
            ->orderBy('created_at')
            ->get()
            ->pluck('created_at')
            ->map(fn ($date) => $date->format('d/m/Y'))
            ->implode("\n");

        return [
            'content' => null,
            'embeds' => [
                [
                    'title' => 'Training Place Automatically Removed',
                    'description' => "The training place for **{$notifiable->name} ({$notifiable->id})** on **{$positionLabel}** has been removed for a fourth failed availability check.",
                    'color' => 15158332,
                    'fields' => [
                        [
                            'name' => 'Failed Check Dates',
                            'value' => $warningDates,
                        ],
                    ],
                    'timestamp' => now()->toIso8601String(),
                ],
            ],
        ];
    }

    public function getChannel(): string
    {
        return $this->availabilityWarning->trainingPlace->trainingTeamDiscordChannelId();
    }

    private function placeLabel($trainingPlace): string
    {
        $position = $trainingPlace->trainingPosition?->position;

        if ($position) {
            return "{$position->name} ({$position->callsign})";
        }

        return $trainingPlace->display_name;
    }
}
