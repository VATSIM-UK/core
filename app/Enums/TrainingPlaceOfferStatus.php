<?php

declare(strict_types=1);

namespace App\Enums;

enum TrainingPlaceOfferStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case UnderReview = 'under_review';
    case Rescinded = 'rescinded';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Accepted => 'Accepted',
            self::Declined => 'Declined',
            self::UnderReview => 'Under Review',
            self::Rescinded => 'Rescinded',
            self::Expired => 'Expired',
        };
    }
}
