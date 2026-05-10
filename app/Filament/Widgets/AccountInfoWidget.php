<?php

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Illuminate\Support\Str;

class AccountInfoWidget extends Widget
{
    protected static ?int $sort = -3;

    protected static bool $isLazy = false;

    protected string|int|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.account-info-widget';

    public static function canView(): bool
    {
        return Filament::auth()->check();
    }

    protected function getViewData(): array
    {
        $atcMap = [
            'S1' => 'New Controller (S1)',
            'S2' => 'Tower (S2)',
            'S3' => 'Approach (S3)',
            'C1' => 'Enroute (C1)',
            'HEATHROW' => 'Heathrow',

            'OBS' => 'New Controller (S1)',
            'TWR' => 'Tower (S2)',
            'APP' => 'Approach (S3)',
            'CTR' => 'Enroute (C1)',
        ];

        $pilotMap = [
            'TFP' => 'The Flying Program (TFP)',
            'P1' => 'Private Pilot License (P1)',
            'P2' => 'Instrument Rating (P2)',
            'P3' => 'Commercial Multi-Engine License (P3)',
            'P4' => 'Air Transport Pilot License (P4)',
        ];

        $user = Filament::auth()->user();
        $permissions = $user->getAllPermissions();

        $atcMentorGroups = $permissions
            ->filter(fn ($perm) => Str::startsWith(($perm->name), 'discord/atc/mentor/'))
            ->map(function ($perm) use ($atcMap) {
                $code = Str::upper(Str::after($perm->name, 'discord/atc/mentor/'));

                return $atcMap[$code] ?? $code;
            })
            ->values();

        $pilotMentorGroups = $permissions
            ->filter(fn ($perm) => Str::startsWith(($perm->name), 'discord/pilot/mentor/'))
            ->map(function ($perm) use ($pilotMap) {
                $code = Str::upper(Str::after($perm->name, 'discord/pilot/mentor/'));

                return $pilotMap[$code] ?? $code;
            })
            ->filter()
            ->values();

        $examinerGroups = $permissions
            ->filter(fn ($perm) => Str::startsWith(($perm->name), 'training.exams.conduct.'))
            ->map(function ($perm) use ($atcMap, $pilotMap) {
                $level = Str::upper(Str::after($perm->name, 'training.exams.conduct.'));
                $atcLevels = ['OBS', 'TWR', 'APP', 'CTR'];

                return [
                    'level' => $level,
                    'label' => array_key_exists($level, $atcMap) ? $atcMap[$level] : $pilotMap[$level] ?? $level,
                    'type' => in_array($level, $atcLevels) ? 'atc' : 'pilot',
                ];
            })->filter()->values();

        $roles = collect([
            $atcMentorGroups->isNotEmpty() ? 'ATC Mentor' : null,
            $pilotMentorGroups->isNotEmpty() ? 'Pilot Mentor' : null,
            $examinerGroups->isNotEmpty() ? 'Examiner' : null,

            $user->hasPermissionTo('admin.access') ? 'Staff' : null,

            $user->hasPermissionTo('discord/moderator') ? 'Moderator' : null,

            $user->hasPermissionTo('discord/dsg') ? 'Division Staff' : null,
        ])->filter()->values();

        $endorsements = $user->endorsements
            ->map(fn ($e) => $e->endorsable->name)
            ->filter()
            ->values();

        $pilotRating = $user->qualifications_pilot->sortByDesc('vatsim')->first();

        $rosterStatus = ($user->roster ?? null);

        return [
            'user' => $user,
            'roles' => $roles,
            'endorsements' => $endorsements,
            'rosterStatus' => $rosterStatus,
            'pilotRating' => $pilotRating,
            'ratingBadge' => fn ($rating) => $this->ratingBadge($rating),
            'panel' => Filament::getCurrentPanel()->getId(),

            'atcMentorGroups' => $atcMentorGroups,
            'pilotMentorGroups' => $pilotMentorGroups,
            'examinerGroups' => $examinerGroups,
        ];
    }

    private function ratingBadge(?string $rating): ?string
    {
        if (! $rating) {
            return null;
        }

        $colors = [
            'OBS' => 'bg-gray-400 text-gray-800',

            'S1' => 'bg-green-200 text-gray-800',
            'S2' => 'bg-blue-200 text-gray-800',
            'S3' => 'bg-indigo-200 text-gray-800',

            'C1' => 'bg-purple-200 text-gray-800',
            'C3' => 'bg-amber-200 text-gray-800',

            'P0' => 'bg-gray-200 text-gray-800',
            'PPL' => 'bg-green-200 text-green-800',
            'IR' => 'bg-blue-200 text-blue-800',
            'CMEL' => 'bg-indigo-200 text-indigo-800',
            'ATPL' => 'bg-purple-200 text-purple-800',
            'FI' => 'bg-amber-200 text-amber-800',
            'FE' => 'bg-pink-200 text-pink-800',

        ];
        $color = $colors[$rating] ?? 'bg-gray-200 text-gray-800';

        return "<span class=\"inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {$color}\">{$rating}</span>";
    }
}
