<?php

namespace App\Enums;

enum QualificationTypeEnum: string
{
    case ATC = 'atc';
    case ATCTraining = 'training_atc';
    case Pilot = 'pilot';
    case PilotTraining = 'training_pilot';
    case MilitaryPilot = 'pilot_military';
    case Admin = 'admin';

    public function human(): string
    {
        return match ($this) {
            self::ATCTraining => 'ATC Training',
            self::PilotTraining => 'Pilot Training',
            self::MilitaryPilot => 'Military Pilot',
            default => $this->name
        };
    }
}
