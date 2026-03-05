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
    public function toMail($notifiable)
    {
        $trainingPosition = $this->trainingPlaceOffer->trainingPosition();
        $offerUrl = route('mship.waiting-lists.place-offer.show', ['token' => $this->trainingPlaceOffer->token]);

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('UK Training Place Offer')
            ->view('emails.training.training_place_offer', [
                'position' => $trainingPosition,
                'offer_url' => $offerUrl,
            ]);
    }
}
