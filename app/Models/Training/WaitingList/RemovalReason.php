<?php

namespace App\Models\Training\WaitingList;

enum RemovalReason: string
{
    case TrainingPlace = 'awarded_training_place';
    case Request = 'requested_removal';
    case Inactivity = 'member_inactive';
    case NonHome = 'member_non_home';
    case FailedRetention = 'failed_retention_check';
    case CancelledVTApplication = 'cancelled_vt_application';
    case DeclinedTrainingPlaceOffer = 'declined_training_place_offer';
    case TrainingPlaceOfferRescinded = 'training_place_offer_rescinded';
    case TrainingPlaceOfferExpired = 'training_place_offer_expired';
    case SelfRemoved = 'self_removed';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::TrainingPlace => 'Member awarded training place',
            self::Request => 'Member requested removal',
            self::Inactivity => 'Member inactive',
            self::NonHome => 'Member is not a home member',
            self::FailedRetention => 'Member failed retention check',
            self::CancelledVTApplication => 'VT application cancelled',
            self::DeclinedTrainingPlaceOffer => 'Declined training place offer',
            self::TrainingPlaceOfferRescinded => 'Training place offer rescinded',
            self::TrainingPlaceOfferExpired => 'Training place offer expired',
            self::SelfRemoved => 'Member removed themselves',
            self::Other => 'Other (please specify)',
        };
    }

    public static function formOptions(): array
    {
        return array_reduce(self::cases(), function ($carry, RemovalReason $item) {
            $carry[$item->value] = $item->label();

            return $carry;
        }, []);
    }
}
