<?php

namespace App\Policies;

use App\Models\Mship\Account;
use App\Models\Mship\Feedback\Feedback;
use Illuminate\Auth\Access\HandlesAuthorization;

class FeedbackPolicy
{
    use HandlesAuthorization;

    private const GUARD = 'web';

    public function viewAny(Account $account)
    {
        $permission = 'feedback.access';

        return $account->checkPermissionTo($permission, self::GUARD);
    }

    public function view(Account $account, Feedback $feedback)
    {
        $feedback->load('form');

        $permission = "feedback.view-type.{$feedback->form->slug}";

        return $account->checkPermissionTo($permission, self::GUARD)
            && ! in_array($feedback->account_id, $account->hiddenFeedbackUsers());
    }

    public function actionFeedback(Account $account)
    {
        return $account->checkPermissionTo('feedback/action', self::GUARD) || $account->checkPermissionTo('feedback.action', self::GUARD);
    }

    public function seeSubmitter(Account $account)
    {
        return $account->checkPermissionTo('feedback/submitter', self::GUARD) || $account->checkPermissionTo('feedback.view-submitter', self::GUARD);
    }
}
