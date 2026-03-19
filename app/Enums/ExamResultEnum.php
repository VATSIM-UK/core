<?php

namespace App\Enums;

enum ExamResultEnum: string
{
    case Pass = 'P';
    case PartialPass = 'PP';
    case Fail = 'F';
    case Incomplete = 'N';

    public function human(): string
    {
        return match ($this) {
            self::Pass => 'Pass',
            self::PartialPass => 'Partial Pass',
            self::Fail => 'Fail',
            self::Incomplete => 'Incomplete',
        };
    }

    public static function atcOptions(): array
    {
        return [
            self::Pass->value => self::Pass->human(),
            self::Fail->value => self::Fail->human(),
            self::Incomplete->value => self::Incomplete->human(),
        ];
    }

    public static function pilotOptions(): array
    {
        return [
            self::Pass->value => self::Pass->human(),
            self::PartialPass->value => self::PartialPass->human(),
            self::Fail->value => self::Fail->human(),
            self::Incomplete->value => self::Incomplete->human(),
        ];
    }
}