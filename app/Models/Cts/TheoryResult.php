<?php

namespace App\Models\Cts;

use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use App\Models\Cts\Member;

class TheoryResult extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    public $timestamps = false;

    /**
     * Get result for the internal Core account_id, also their CId
     * and handle the logic where a member_id in CTS is different to that
     * of the account_id in Core.
     */
    public static function forAccount(int $account_id): ?Collection
    {
        try {
            $memberId = Member::where('cid', $account_id)->firstOrFail()->id;
        } catch (ModelNotFoundException) {
            Log::warning("No member found for account_id {$account_id}. Likely sync problems.");

            return null;
        }

        // return the first part of a query to get results for a given member.
        // providing a member_id is found, otherwise return null.
        return self::where('student_id', $memberId)->get();
    }

    public function getNameAttribute()
    {
        try {
            $member = Member::where('id', $this->student_id)->firstOrFail();
            $account = Account::find($member->cid);

            return $account?->name ?? 'Unknown';
        } catch (ModelNotFoundException) {
            // Log::warning("No member found for account_id {$student_id}. Likely sync problems.");

            return 'Unknown fail';
        }
    }
}
