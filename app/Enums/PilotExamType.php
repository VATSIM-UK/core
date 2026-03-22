<?php

namespace App\Enums;

enum PilotExamType: string
{
    case P1 = 'P1';
    case P2 = 'P2';
    case P3 = 'P3';

    public function label(): string
    {
        return match ($this) {
            self::P1 => 'P1_PPL(A)',
            self::P2 => 'P2_SEIR(A)',
            self::P3 => 'P3_CMEL(A)',
        };
    }

    public function prerequisiteRating(): string
    {
        return match ($this) {
            self::P1 => 'P0',
            self::P2 => 'P1',
            self::P3 => 'P2',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labelFor(string $type): string
    {
        return self::from($type)->label();
    }
}
