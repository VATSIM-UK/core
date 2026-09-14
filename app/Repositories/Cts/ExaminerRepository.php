<?php

namespace App\Repositories\Cts;

use App\Models\Cts\Member;
use App\Models\Mship\Account;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ExaminerRepository
{
    private const EXAMINER_ROLE_MAP = [
        'obs' => ['ATC Examiner (OBS)'],
        'twr' => ['ATC Examiner (TWR)'],
        'app' => ['ATC Examiner (APP)'],
        'ctr' => ['ATC Examiner (CTR)'],
        'p1' => ['Pilot Examiner (P1)'],
        'p2' => ['Pilot Examiner (P2)'],
        'p3' => ['Pilot Examiner (P3)'],
        'atc' => [
            'ATC Examiner (OBS)',
            'ATC Examiner (TWR)',
            'ATC Examiner (APP)',
            'ATC Examiner (CTR)',
        ],
        'pilot' => [
            'Pilot Examiner (P1)',
            'Pilot Examiner (P2)',
            'Pilot Examiner (P3)',
        ],
    ];

    /* Core reusable fetcher. */
    private function getExaminersByScope(string $scope): Collection
    {

        $roleNames = self::EXAMINER_ROLE_MAP[$scope] ?? null;

        if ($roleNames === null) {
            throw new InvalidArgumentException("Unknown scope '{$scope}'.");
        }

        return Account::query()
            ->role($roleNames)
            ->get()
            ->unique('id')
            ->sortBy('name')
            ->values();
    }

    public function getExaminerDetailsByScope(string $scope): Collection
    {

        $accounts = $this->getExaminersByScope($scope);

        if ($accounts->isEmpty()) {
            return collect();
        }

        $members = Member::query()
            ->whereIn('cid', $accounts->pluck('id'))
            ->get()
            ->keyBy('cid');

        return $accounts

            ->map(

                function (Account $account) use ($members) {

                    $member = $members->get($account->id);

                    if (! $member) {
                        return null;
                    }

                    return [
                        'cid' => $account->id,
                        'name' => $account->name,
                        'id' => $member->id,
                    ];
                }
            )
            ->filter()
            ->sortBy('name')
            ->values();
    }

    public function getObsExaminers(): Collection
    {
        return $this->getExaminersByScope('obs')->pluck('id')->values();
    }

    public function getTwrExaminers(): Collection
    {
        return $this->getExaminersByScope('twr')->pluck('id')->values();
    }

    public function getAppExaminers(): Collection
    {
        return $this->getExaminersByScope('app')->pluck('id')->values();
    }

    public function getCtrExaminers(): Collection
    {
        return $this->getExaminersByScope('ctr')->pluck('id')->values();
    }

    public function getAtcExaminers(): Collection
    {
        return $this->getExaminersByScope('atc')->pluck('id')->values();
    }

    public function getPilotExaminers(): Collection
    {
        return $this->getExaminersByScope('pilot')->pluck('id')->values();
    }
}
