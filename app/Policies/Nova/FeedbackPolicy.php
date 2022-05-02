<?php

namespace App\Policies\Nova;

use App\Models\Mship\Account;
use App\Models\Mship\Feedback\Feedback;
use App\Policies\BasePolicy;

class FeedbackPolicy extends BasePolicy
{
    private const GUARD = 'web';

    public function before(Account $account, $policy)
    {
        if (parent::before($account, $policy)) {
            return true;
        }
    }

    public function view(Account $account, Feedback $feedback)
    {
        return $account->checkPermissionTo("feedback/view/{$feedback->form->slug}", self::GUARD)
            && ! in_array($feedback->account_id, $account->hiddenFeedbackUsers());
    }

    public function actionFeedback(Account $account)
    {
        return $account->checkPermissionTo('feedback/action', self::GUARD);
    }

    public function seeSubmitter(Account $account)
    {
        return $account->checkPermissionTo('feedback/submitter', self::GUARD);
    }
}
