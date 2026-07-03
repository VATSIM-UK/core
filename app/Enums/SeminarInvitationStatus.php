<?php

declare(strict_types=1);

namespace App\Enums;

enum SeminarInvitationStatus: string
{
    case Sent = 'sent';
    case Attending = 'attending';
    case NotInterested = 'not_interested';
    case CannotAttend = 'cannot_attend';
    case Expired = 'expired';
    case RemovedNoResponse = 'removed_no_response';
    case RemovedTwoCannotAttend = 'removed_two_cannot_attend';

    public function label(): string
    {
        return match ($this) {
            self::Sent => 'Sent',
            self::Attending => 'Attending',
            self::NotInterested => 'Not Interested',
            self::CannotAttend => 'Cannot Attend',
            self::Expired => 'Expired',
            self::RemovedNoResponse => 'Removed (No Response)',
            self::RemovedTwoCannotAttend => 'Removed (Two Cannot Attend)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Sent => 'gray',
            self::Attending => 'success',
            self::NotInterested => 'danger',
            self::CannotAttend => 'warning',
            self::Expired => 'danger',
            self::RemovedNoResponse => 'danger',
            self::RemovedTwoCannotAttend => 'danger',
        };
    }

    public function isResponded(): bool
    {
        return in_array($this, [
            self::Attending,
            self::NotInterested,
            self::CannotAttend,
        ], true);
    }
}
