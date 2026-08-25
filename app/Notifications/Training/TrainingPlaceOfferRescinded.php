<?php

namespace App\Notifications\Training;

use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use App\Models\Training\WaitingList;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to a member when their training place offer is rescinded by staff.
 * The member retains their waiting list position.
 */
class TrainingPlaceOfferRescinded extends Notification
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
        $offer = $this->trainingPlaceOffer;
        $account = $offer->waitingListAccount->account;
        $isPilot = $offer->department === WaitingList::PILOT_DEPARTMENT;

        $viewData = [
            'recipient' => $notifiable,
            'account' => $account,
            'reason' => $this->reason,
        ];

        if ($isPilot) {
            $viewData['course_name'] = $offer->display_name;
        } else {
            $viewData['position'] = $offer->trainingPosition?->position;
        }

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('UK Training Place Offer Rescinded')
            ->view(
                $isPilot
                    ? 'emails.training.training_place_offer_rescinded_pilot'
                    : 'emails.training.training_place_offer_rescinded',
                $viewData
            );
    }
}
