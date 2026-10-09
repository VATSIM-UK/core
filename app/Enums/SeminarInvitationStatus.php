<?php

declare(strict_types=1);

namespace App\Enums;

enum SeminarInvitationStatus: string
{
    case Sent = 'sent';
    case Attending = 'attending';
    case NotInterested = 'not_interested';
    case CannotAttend = 'cannot_attend';
    case RemovedNoResponse = 'removed_no_response';
    case RemovedTwoCannotAttend = 'removed_two_cannot_attend';
    case ShortNoticeNoResponse = 'short_notice_no_response';

    public function label(): string
    {
        return match ($this) {
            self::Sent => 'Sent',
            self::Attending => 'Attending',
            self::NotInterested => 'Not Interested',
            self::CannotAttend => 'Cannot Attend',
            self::RemovedNoResponse => 'Removed (No Response)',
            self::RemovedTwoCannotAttend => 'Removed (Two Cannot Attend)',
            self::ShortNoticeNoResponse => 'No Response (Short Notice)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Sent => 'gray',
            self::Attending => 'success',
            self::NotInterested => 'danger',
            self::CannotAttend => 'warning',
            self::RemovedNoResponse => 'danger',
            self::RemovedTwoCannotAttend => 'danger',
            self::ShortNoticeNoResponse => 'gray',
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
