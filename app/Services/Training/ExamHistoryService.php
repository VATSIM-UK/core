<?php

namespace App\Services\Training;

use App\Models\Mship\Account;
use App\Repositories\Cts\ExamResultRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ExamHistoryService
{
    public function __construct(private readonly ExamResultRepository $examResultRepository) {}

    public function getExamHistoryQuery(Account $user): Builder
    {
        return $this->examResultRepository->getExamHistoryQueryForLevels($this->getTypesToShow($user));
    }

    public function getTypesToShow(Account $user): Collection
    {
        $permissionMap = [
            'obs' => 'training.exams.conduct.obs',
            'twr' => 'training.exams.conduct.twr',
            'app' => 'training.exams.conduct.app',
            'ctr' => 'training.exams.conduct.ctr',
        ];

        return collect($permissionMap)
            ->filter(fn (string $permission) => $user->can($permission))
            ->keys();
    }

    public function getResultBadgeColor(string $result): string
    {
        return match ($result) {
            'Passed' => 'success',
            'Failed' => 'danger',
            'Incomplete' => 'warning',
            default => 'gray',
        };
    }

    public function applyExamDateFilter(Builder $query, array $data): Builder
    {
        return $query
            ->when($data['exam_date_from'] ?? null, fn (Builder $query, string $date) => $query->whereHas('examBooking', fn (Builder $q) => $q->whereDate('taken_date', '>=', $date)))
            ->when($data['exam_date_to'] ?? null, fn (Builder $query, string $date) => $query->whereHas('examBooking', fn (Builder $q) => $q->whereDate('taken_date', '<=', $date)));
    }

    public function applyPositionFilter(Builder $query, array $data): Builder
    {
        return $query->when($data['position'] ?? null, function (Builder $query, array $positions) {
            $query->whereHas('examBooking', function (Builder $q) use ($positions) {
                $q->where(function (Builder $subQuery) use ($positions) {
                    foreach ($positions as $position) {
                        $subQuery->orWhere('position_1', 'LIKE', "%{$position}%");
                    }
                });
            });
        });
    }

    public function applyConductedByMeFilter(Builder $query, array $data, int $userCid): Builder
    {
        if (! ($data['conducted_by_me'] ?? false)) {
            return $query;
        }

        return $query->whereHas('examBooking.examiners', function (Builder $q) use ($userCid) {
            $q->where(function (Builder $subQuery) use ($userCid) {
                $subQuery->whereHas('primaryExaminer', fn (Builder $sq) => $sq->where('cid', $userCid))
                    ->orWhereHas('secondaryExaminer', fn (Builder $sq) => $sq->where('cid', $userCid))
                    ->orWhereHas('traineeExaminer', fn (Builder $sq) => $sq->where('cid', $userCid));
            });
        });
    }
}
