<?php

namespace App\Services\Mship;

use App\Models\Mship\Account;
use Illuminate\Support\Collection;

class FeedbackViewService
{
    public function getSentFeedback(Account $account): Collection
    {
        return $account->feedback()
            ->sent()
            ->get()
            ->reverse();
    }
}
