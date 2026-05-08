<?php

namespace App\Filament\Training\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Illuminate\Support\Str;

class AccountInfoWidget extends Widget
{
    protected static ?int $sort = -3;

    protected static bool $isLazy = false;

    protected string $view = 'filament.training.widgets.account-info-widget';

    public static function canView(): bool
    {
        return Filament::auth()->check();
    }

    protected function getViewData(): array
    {
        $user = Filament::auth()->user();
        $permissions = $user->getAllPermissions();

        $isATCMentor = $permissions->contains(function ($perm) {
            return Str::startsWith(($perm->name), 'discord/atc/mentor/');
        });

        $isPilotMentor = $permissions->contains(function ($perm) {
            return Str::startsWith(($perm->name), 'discord/pilot/mentor');
        });

        $roles = collect([
            $isATCMentor ? 'ATC Mentor' : null,
            $isPilotMentor ? 'Pilot Mentor' : null,

            $user->hasPermissionTo('training.exams.access') ? 'Examiner' : null,

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

            'P0' => 'bg-emerald-200 text-emerald-800',
            'P1' => 'bg-emerald-200 text-emerald-800',
            'P2' => 'bg-emerald-200 text-emerald-800',
            'P3' => 'bg-emerald-200 text-emerald-800',
            'P4' => 'bg-emerald-200 text-emerald-800',
        ];
        $color = $colors[$rating] ?? 'bg-gray-200 text-gray-800';

        return "<span class=\"inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {$color}\">{$rating}</span>";
    }
}
