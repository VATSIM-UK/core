<?php

namespace App\Enums;

enum ExamResultEnum: string
{
    case Pass = 'P';
    case Fail = 'F';
    case Incomplete = 'N';

    public function human(): string
    {
        return match ($this) {
            self::Pass => 'Pass',
            self::Fail => 'Fail',
            self::Incomplete => 'Incomplete',
        };
    }
}
