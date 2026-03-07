<?php

namespace App\Notifications\Training;

use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to a member when their training place offer is rescinded by staff and they are removed from the waiting list.
 * The member retains their waiting list position.
 */
class TrainingPlaceOfferRescindedAndRemoved extends Notification
{
    public function __construct(
        public TrainingPlaceOffer $trainingPlaceOffer,
        public string $reason,
    ) {
        //
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $account = $this->trainingPlaceOffer->waitingListAccount->account;
        $position = $this->trainingPlaceOffer->trainingPosition->position;

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('UK Training Place Offer Rescinded')
            ->view('emails.training.training_place_offer_rescinded_and_removed', [
                'recipient' => $notifiable,
                'account' => $account,
                'position' => $position,
                'reasons' => $this->reason,
            ]);
    }
}