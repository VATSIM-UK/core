<?php

namespace App\Repositories\Cts;

use App\Models\Cts\ExaminerSettings;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ExaminerRepository
{
    /**
     * Core reusable fetcher.
     */
    private function getExaminersByScope(string $scope): Collection
    {
        $scopeMethod = 'scope'.ucfirst($scope);
        if (! method_exists(ExaminerSettings::class, $scopeMethod)) {
            throw new InvalidArgumentException("Unknown scope '{$scope}'.");
        }

        return ExaminerSettings::with('member')
            ->{$scope}()
            ->whereHas('member', fn ($q) => $q->where('examiner', true))
            ->get()
            ->pluck('member.cid')
            ->sort()
            ->values();
    }

    public function getObsExaminers(): Collection
    {
        return $this->getExaminersByScope('obs');
    }

    public function getTwrExaminers(): Collection
    {
        return $this->getExaminersByScope('twr');
    }

    public function getAppExaminers(): Collection
    {
        return $this->getExaminersByScope('app');
    }

    public function getCtrExaminers(): Collection
    {
        return $this->getExaminersByScope('ctr');
    }

    public function getAtcExaminers(): Collection
    {
        return $this->getExaminersByScope('atc');
    }

    public function getPilotExaminers(): Collection
    {
        return $this->getExaminersByScope('pilot');
    }
}
