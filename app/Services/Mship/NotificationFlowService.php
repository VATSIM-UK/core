<?php

namespace App\Services\Mship;

use App\Models\Mship\Account;
use App\Services\Mship\DTO\NotificationAcknowledgeRedirectData;
use App\Services\Mship\DTO\NotificationAcknowledgeResult;

class NotificationFlowService
{
    public function acknowledge(Account $account, mixed $notification, string $redirectPath, ?string $forcedReturnUrl): NotificationAcknowledgeResult
    {
        if ($account->hasReadNotification($notification)) {
            return NotificationAcknowledgeResult::alreadyRead();
        }

        $account->readSystemNotifications()->attach($notification->id);

        if ($this->canLeaveNotificationFlow($account) && $forcedReturnUrl) {
            return NotificationAcknowledgeResult::forcedReturn($forcedReturnUrl);
        }

        return NotificationAcknowledgeResult::continue($redirectPath);
    }

    public function buildAcknowledgeRedirect(NotificationAcknowledgeResult $result): NotificationAcknowledgeRedirectData
    {
        if ($result->isAlreadyRead()) {
            return new NotificationAcknowledgeRedirectData('mship.manage.dashboard');
        }

        return new NotificationAcknowledgeRedirectData('', $result->redirectUrl);
    }

    public function allowedToLeaveNotificationList(Account $account, bool $hasForcedReturnUrl): bool
    {
        if (! $hasForcedReturnUrl) {
            return true;
        }

        return $this->canLeaveNotificationFlow($account);
    }

    public function canLeaveNotificationFlow(Account $account): bool
    {
        return ! $account->has_unread_important_notifications && ! $account->get_unread_must_read_notifications;
    }
}
