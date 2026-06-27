<?php

declare(strict_types=1);

namespace App\Services\Training;

use App\Models\Cts\Member;
use App\Models\Cts\PracticalResult;
use App\Models\Cts\Session;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Illuminate\Support\Collection;

class TrainingGroupStatisticsService
{
    public const ATC_CATEGORY_EXAM_MAP = [
        'OBS to S1 Training' => 'OBS',
        'S2 Training' => 'TWR',
        'S3 Training' => 'APP',
        'C1 Training' => 'CTR',
    ];

    public function __construct(
        private readonly MentorPermissionService $mentorPermissionService,
    ) {}

    public function statisticsForCategory(string $category): array
    {
        return [
            'category' => $category,
            'active_training_places' => $this->activeTrainingPlacesCount($category),
            'average_sessions_to_rating' => $this->averageSessionsToRating($category),
            'average_training_duration_days' => $this->averageTrainingDurationDays($category),
            'exam_first_pass_rate' => $this->examFirstPassRate($category),
        ];
    }

    public function activeTrainingPlacesCount(string $category): int
    {
        return TrainingPlace::query()
            ->whereHas('trainingPosition', fn ($query) => $query->where('category', $category))
            ->count();
    }

    public function averageSessionsToRating(string $category): ?float
    {
        $completedPlaces = $this->completedTrainingPlaces($category);

        if ($completedPlaces->isEmpty()) {
            return null;
        }

        $callsigns = $this->callsignsForCategory($category);

        if ($callsigns === []) {
            return null;
        }

        $sessionCounts = $completedPlaces->map(function (TrainingPlace $place) use ($callsigns): ?int {
            $ctsMemberId = $place->account?->member?->id;

            if ($ctsMemberId === null || $place->deleted_at === null) {
                return null;
            }

            return Session::query()
                ->where('student_id', $ctsMemberId)
                ->whereIn('position', $callsigns)
                ->whereNull('cancelled_datetime')
                ->where('noShow', 0)
                ->where('session_done', 1)
                ->whereDate('taken_date', '>=', $place->created_at->toDateString())
                ->whereDate('taken_date', '<=', $place->deleted_at->toDateString())
                ->count();
        })->filter(fn (?int $count): bool => $count !== null);

        if ($sessionCounts->isEmpty()) {
            return null;
        }

        return round($sessionCounts->avg(), 1);
    }

    public function averageTrainingDurationDays(string $category): ?float
    {
        $completedPlaces = $this->completedTrainingPlaces($category);

        if ($completedPlaces->isEmpty()) {
            return null;
        }

        $durations = $completedPlaces
            ->filter(fn (TrainingPlace $place): bool => $place->deleted_at !== null)
            ->map(fn (TrainingPlace $place): float => $place->created_at->diffInRealDays($place->deleted_at));

        if ($durations->isEmpty()) {
            return null;
        }

        return round($durations->avg(), 0);
    }

    public function examFirstPassRate(string $category): ?int
    {
        $examType = self::ATC_CATEGORY_EXAM_MAP[$category] ?? null;

        if ($examType === null) {
            return null;
        }

        $accountIds = $this->completedTrainingPlaces($category)
            ->pluck('account_id')
            ->unique()
            ->values();

        if ($accountIds->isEmpty()) {
            return null;
        }

        $ctsMemberIds = Member::query()
            ->whereIn('cid', $accountIds)
            ->pluck('id');

        if ($ctsMemberIds->isEmpty()) {
            return null;
        }

        $firstAttempts = PracticalResult::query()
            ->where('exam', $examType)
            ->whereIn('student_id', $ctsMemberIds)
            ->orderBy('date')
            ->get()
            ->groupBy('student_id')
            ->map(fn (Collection $results) => $results->first());

        if ($firstAttempts->isEmpty()) {
            return null;
        }

        $passed = $firstAttempts
            ->filter(fn (PracticalResult $result): bool => $result->result === PracticalResult::PASSED)
            ->count();

        return (int) round($passed / $firstAttempts->count() * 100);
    }

    private function callsignsForCategory(string $category): array
    {
        return $this->mentorPermissionService->getAllCtsCallsignsForCategory($category);
    }

    private function completedTrainingPlaces(string $category): Collection
    {
        return TrainingPlace::onlyTrashed()
            ->whereHas('trainingPosition', fn ($query) => $query->where('category', $category))
            ->with(['account', 'trainingPosition'])
            ->get();
    }
}
