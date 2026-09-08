<?php

namespace App\Repositories\Cts;

use App\Models\Cts\Member;
use App\Models\Mship\Account;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ExaminerRepository
{
    /**
     * Examiner roles are managed in Core by the Manage Examiners page.
     *
     * The short scope names are used by exam bookings and are deliberately
     * kept separate from the full Spatie role names used by Core accounts.
     */
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

    /**
     * Get Core accounts assigned to examiner roles for the requested scope.
     *
     * Examiner role assignments live in the Core database. We then look up
     * the corresponding CTS member records because the existing CTS exam
     * tables store CTS member IDs rather than Core account IDs/CIDs.
     */
    private function getExaminersByScope(string $scope): Collection
    {

        // Lookup the supplied scope in EXAMINER_ROLE_MAP
        // Return null if the scope is not defined in the map
        $roleNames = self::EXAMINER_ROLE_MAP[$scope] ?? null;

        // Throw an exception if the scope is not recognized
        if ($roleNames === null) {
            throw new InvalidArgumentException("Unknown scope '{$scope}'.");
        }

        // Query Core accounts with the specified examiner roles
        // Return the unique, sorted by name collection of accounts
        return Account::query()
            ->role($roleNames)
            ->get() // Execute the query and retrieve the collection of accounts
            ->unique('id')
            ->sortBy('name')
            ->values(); // Reindex the collection after sorting
    }

    public function getExaminerDetailsByScope(string $scope): Collection
    {
        // Retrieve Core accounts for the requested scope
        // Using the private helper method getExaminersByScope()
        $accounts = $this->getExaminersByScope($scope);

        // Return an empty collection if no accounts were found for the scope
        if ($accounts->isEmpty()) {
            return collect();
        }

        // Member IDs belong to CTS, so this lookup intentionally uses the
        // CTS model and matches records by the shared VATSIM CID.
        $members = Member::query()
            ->whereIn('cid', $accounts->pluck('id')) // Match CTS members by VATSIM CID
            ->get()
            ->keyBy('cid'); // Key the collection by VATSIM CID for easy lookup

        // Map Core accounts to CTS member details, filtering out accounts without corresponding CTS members
        return $accounts

            // Map each Core account to its corresponding CTS member details
            // this ensures that only Core accounts with corresponding CTS member
            // records are included in the final collection
            ->map(

                // Create an anonymous function to map each Core account to its
                // corresponding CTS member details
                function (Account $account) use ($members) {

                    // Look up the corresponding CTS member for the current Core account
                    $member = $members->get($account->id);

                    // A Core examiner without a CTS member cannot be stored in
                    // practical_examiners, which expects a CTS member ID.
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
