<?php

namespace App\Models\Training\WaitingList;

enum RemovalReason: string
{
    case TrainingPlace = 'awarded_training_place';
    case Request = 'requested_removal';
    case Inactivity = 'member_inactive';
    case NonHome = 'member_non_home';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::TrainingPlace => 'Member awarded training place',
            self::Request => 'Member requested removal',
            self::Inactivity => 'Member inactive',
            self::NonHome => 'Member is not a home member',
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
