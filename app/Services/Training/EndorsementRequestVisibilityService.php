<?php

declare(strict_types=1);

namespace App\Services\Training;

use App\Models\Mship\Account;
use App\Models\Mship\Account\EndorsementRequest;
use Illuminate\Database\Eloquent\Builder;

/**
 * Decides which endorsement requests a member may see.
 */
class EndorsementRequestVisibilityService
{
    /**
     * Determine whether the member may see requests raised for any Training Group.
     */
    public function seesAll(Account $user): bool
    {
        return $user->can('viewAll', EndorsementRequest::class);
    }

    /**
     * Narrow the given query to the requests the member may see.
     */
    public function scope(Builder $query, Account $user): Builder
    {
        if ($this->seesAll($user)) {
            return $query;
        }

        $studentAccountIds = $user->studentAccountIdsInMentoringScope();

        return $query->where(function (Builder $query) use ($user, $studentAccountIds): void {
            $query->where('requested_by', $user->id);

            if ($studentAccountIds !== []) {
                $query->orWhereIn('account_id', $studentAccountIds);
            }
        });
    }

    /**
     * Determine whether the member may see the given request.
     */
    public function canView(Account $user, EndorsementRequest $endorsementRequest): bool
    {
        if ($this->seesAll($user)) {
            return true;
        }

        if ((int) $endorsementRequest->requested_by === (int) $user->id) {
            return true;
        }

        return in_array(
            (int) $endorsementRequest->account_id,
            $user->studentAccountIdsInMentoringScope(),
            true,
        );
    }
}
