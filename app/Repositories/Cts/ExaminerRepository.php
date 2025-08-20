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
    private function _getExaminersByScope(string $scope): Collection

    {
        // We capitalize here so that the scope methods are readable.
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
        return $this->_getExaminersByScope('obs');
    }

    public function getTwrExaminers(): Collection
    {
        return $this->_getExaminersByScope('twr');
    }

    public function getAppExaminers(): Collection
    {
        return $this->_getExaminersByScope('app');
    }

    public function getCtrExaminers(): Collection
    {
        return $this->_getExaminersByScope('ctr');
    }

    public function getAtcExaminers(): Collection
    {
        return $this->_getExaminersByScope('atc');
    }

    public function getPilotExaminers(): Collection
    {
        return $this->_getExaminersByScope('pilot');
    }
}
