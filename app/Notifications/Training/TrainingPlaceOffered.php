<?php

namespace App\Notifications\Training;

use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use App\Models\Training\WaitingList;
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
     */
    public function toMail($notifiable): MailMessage
    {
        $offer = $this->trainingPlaceOffer;
        $account = $offer->waitingListAccount->account;
        $isPilot = $offer->department === WaitingList::PILOT_DEPARTMENT;

        $viewData = [
            'recipient' => $notifiable,
            'account' => $account,
            'offer' => $offer,
            'accept_url' => route('mship.waiting-lists.training-place-offer.accept', ['token' => $offer->token]),
            'decline_url' => route('mship.waiting-lists.training-place-offer.decline', ['token' => $offer->token]),
        ];

        if ($isPilot) {
            $viewData['course_name'] = $offer->display_name;
        } else {
            $viewData['position'] = $offer->trainingPosition?->position;
        }

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('UK Training Place Offer')
            ->view(
                $isPilot
                    ? 'emails.training.training_place_offer_pilot'
                    : 'emails.training.training_place_offer',
                $viewData
            );
    }
}
