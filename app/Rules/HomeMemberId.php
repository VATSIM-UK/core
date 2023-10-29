<?php

namespace App\Rules;

use App\Models\Mship\Account;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class HomeMemberId implements InvokableRule
{
    public function __invoke($attribute, $value, $fail)
    {
        try {
            $account = Account::findOrFail($value);

            if (! $account->primary_state->isDivision) {
                $fail('The specified member is not a home UK member.');
            }
        } catch (ModelNotFoundException $e) {
            $fail('The specified member was not found.');
        }
    }
}
