<?php

namespace App\Notifications\Training;

use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * This notification is sent to an account when a admin offers them a training place
 */
class TrainingPlaceOffered extends Notification
{
    public function __construct(public TrainingPlaceOffer $trainingPlaceOffer)
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
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $account = $this->trainingPlaceOffer->waitingListAccount->account;
        $position = $this->trainingPlaceOffer->trainingPosition->position;
        $offer = $this->trainingPlaceOffer;

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('UK Training Place Offer')
            ->view('emails.training.training_place_offer', [
                'recipient' => $notifiable,
                'account' => $account,
                'position' => $position,
                'offer' => $offer,
                'accept_url' => route('mship.waiting-lists.training-place-offer.accept', ['token' => $offer->token]),
                'decline_url' => route('mship.waiting-lists.training-place-offer.decline', ['token' => $offer->token]),
            ]);
    }
}
