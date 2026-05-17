<?php

declare(strict_types=1);

namespace Database\Seeders\LocalDevelopment\Training\Concerns;

use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use Illuminate\Support\Collection;

/**
 * Creates a core {@see Account} and CTS {@see Member} sharing the same numeric id/cid.
 *
 * @see database/seeders/LocalDevelopment/README.md
 */
trait CreatesLinkedAccount
{
    /**
     * @param  list<string>  $qualificationCodes  mship qualification codes (e.g. S1)
     */
    protected function createLinkedAccount(
        int $cid,
        string $firstName,
        string $lastName,
        string $email,
        array $qualificationCodes = ['S1'],
    ): Account {
        $account = Account::query()->firstOrCreate(
            ['id' => $cid],
            [
                'name_first' => $firstName,
                'name_last' => $lastName,
                'email' => $email,
            ],
        );

        $this->syncQualifications($account, $qualificationCodes);

        Member::query()->firstOrCreate(
            ['cid' => $cid],
            [
                'id' => $cid,
                'name' => trim("{$firstName} {$lastName}"),
                'joined' => now(),
                'joined_div' => now(),
            ],
        );

        return $account->fresh();
    }

    /**
     * @param  list<string>  $qualificationCodes
     */
    private function syncQualifications(Account $account, array $qualificationCodes): void
    {
        $qualificationIds = Collection::make($qualificationCodes)
            ->map(fn (string $code) => Qualification::query()->where('code', $code)->value('id'))
            ->filter()
            ->all();

        if ($qualificationIds !== []) {
            $account->qualifications()->syncWithoutDetaching($qualificationIds);
        }
    }
}
