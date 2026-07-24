<?php

namespace App\Services\Admin;

use App\Models\VisitTransfer\Application;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;

class VisitTransferStats
{
    private static function baseQuery(?int $type, Carbon $start, Carbon $end): Builder
    {
        return Application::query()
            ->when($type, fn (Builder $q) => $q->where('type', $type))
            ->whereBetween('created_at', [$start, $end])
            ->whereNotIn('status', [
                Application::STATUS_WITHDRAWN,
                Application::STATUS_LAPSED,
                Application::STATUS_EXPIRED,
            ]);
    }

    public static function totals(?int $type, Carbon $start, Carbon $end): array
    {
        $breakdown = self::baseQuery($type, $start, $end)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $accepted = ($breakdown[Application::STATUS_ACCEPTED] ?? 0) + ($breakdown[Application::STATUS_COMPLETED] ?? 0);
        $rejected = $breakdown[Application::STATUS_REJECTED] ?? 0;
        $decided = $accepted + $rejected;

        return [
            'total' => $breakdown->sum(),
            'in_progress' => $breakdown[Application::STATUS_IN_PROGRESS] ?? 0,
            'submitted' => $breakdown[Application::STATUS_SUBMITTED] ?? 0,
            'under_review' => $breakdown[Application::STATUS_UNDER_REVIEW] ?? 0,
            'accepted' => $breakdown[Application::STATUS_ACCEPTED] ?? 0,
            'completed' => $breakdown[Application::STATUS_COMPLETED] ?? 0,
            'rejected' => $rejected,
            'cancelled' => $breakdown[Application::STATUS_CANCELLED] ?? 0,
            'acceptance_rate' => $decided > 0 ? round(($accepted / $decided) * 100, 1) : null,
        ];
    }

    public static function averageDaysToDecision(?int $type, Carbon $start, Carbon $end): ?float
    {
        $avg = Application::query()
            ->when($type, fn (Builder $q) => $q->where('type', $type))
            ->whereIn('status', [Application::STATUS_ACCEPTED, Application::STATUS_COMPLETED, Application::STATUS_REJECTED])
            ->whereBetween('updated_at', [$start, $end])
            ->whereNotNull('submitted_at')
            ->selectRaw('AVG(DATEDIFF(updated_at, submitted_at)) as avg_days')
            ->value('avg_days');

        return $avg !== null ? round((float) $avg, 1) : null;
    }

    public static function dailyTrend(?int $type, Carbon $start, Carbon $end): array
    {
        $rows = self::baseQuery($type, $start, $end)
            ->selectRaw('DATE(created_at) as day, count(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return collect(CarbonPeriod::create($start->copy()->startOfDay(), $end->copy()->endOfDay()))
            ->map(fn ($date) => [
                'day' => $date->toDateString(),
                'total' => (int) ($rows[$date->toDateString()] ?? 0),
            ])
            ->toArray();
    }

    public static function byFacility(?int $type, Carbon $start, Carbon $end): array
    {
        return self::baseQuery($type, $start, $end)
            ->whereNotNull('facility_id')
            ->with('facility:id,name')
            ->get()
            ->groupBy('facility_id')
            ->map(fn ($apps) => [
                'name' => $apps->first()->facility?->name ?? 'Unknown Facility',
                'total' => $apps->count(),
                'accepted' => $apps->whereIn('status', [Application::STATUS_ACCEPTED, Application::STATUS_COMPLETED])->count(),
                'rejected' => $apps->where('status', Application::STATUS_REJECTED)->count(),
            ])
            ->sortByDesc('total')
            ->values()
            ->toArray();
    }

    public static function byRating(?int $type, Carbon $start, Carbon $end): array
    {
        $ratingOrder = [
            'Student 1' => 1,
            'Student 2' => 2,
            'Student 3' => 3,
            'Controller 1' => 4,
            'Controller 3' => 5,
        ];

        return self::baseQuery($type, $start, $end)
            ->with('account')
            ->get()
            ->groupBy(fn ($app) => $app->account?->qualification_atc?->id ?? 'unknown')
            ->map(function ($apps) {
                $accepted = $apps->whereIn('status', [Application::STATUS_ACCEPTED, Application::STATUS_COMPLETED])->count();
                $rejected = $apps->where('status', Application::STATUS_REJECTED)->count();
                $decided = $accepted + $rejected;

                return [
                    'name' => $apps->first()->account?->qualification_atc?->name_long ?? 'Unknown Rating',
                    'total' => $apps->count(),
                    'submitted' => $apps->where('status', Application::STATUS_SUBMITTED)->count(),
                    'under_review' => $apps->where('status', Application::STATUS_UNDER_REVIEW)->count(),
                    'in_progress' => $apps->where('status', Application::STATUS_IN_PROGRESS)->count(),
                    'accepted' => $accepted,
                    'rejected' => $rejected,
                    'cancelled' => $apps->where('status', Application::STATUS_CANCELLED)->count(),
                    'acceptance_rate' => $decided > 0 ? round(($accepted / $decided) * 100, 1) : null,
                ];
            })
            ->sortBy(fn ($row) => $ratingOrder[$row['name']] ?? PHP_INT_MAX)
            ->values()
            ->toArray();
    }

    public static function currentlyWaitingByRating(?int $type): array
    {
        $ratingOrder = [
            'Student 1' => 1,
            'Student 2' => 2,
            'Student 3' => 3,
            'Controller 1' => 4,
            'Controller 3' => 5,
        ];

        return \App\Models\Training\WaitingList\WaitingListAccount::query()
            ->whereNull('deleted_at')
            ->when($type, fn ($q) => $q->whereHas('waitingList.facility', fn ($f) => $f->where('type', $type)))
            ->with('account')
            ->get()
            ->groupBy(fn ($wla) => $wla->account?->qualification_atc?->id ?? 'unknown')
            ->map(fn ($group) => [
                'name' => $group->first()->account?->qualification_atc?->name_long ?? 'Unknown Rating',
                'waiting' => $group->count(),
            ])
            ->sortBy(fn ($row) => $ratingOrder[$row['name']] ?? PHP_INT_MAX)
            ->values()
            ->keyBy('name')
            ->toArray();
    }
}
