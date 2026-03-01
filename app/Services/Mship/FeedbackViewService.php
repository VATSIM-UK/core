<?php

namespace App\Services\Mship;

use App\Models\Mship\Account;
use App\Services\Mship\DTO\FeedbackDisplayData;
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

    public function getDisplayData(Account $account): FeedbackDisplayData
    {
        $feedback = $this->getSentFeedback($account);

        if ($feedback->isEmpty()) {
            return new FeedbackDisplayData(false, errorMessage: 'You have no feedback available to view at this time.');
        }

        return new FeedbackDisplayData(true, $feedback);
    }
}
