<?php

namespace App\Http\Controllers\Mship;

use App\Services\Mship\NotificationFlowService;
use Illuminate\Support\Facades\Session;

class Notification extends \App\Http\Controllers\BaseController
{
    protected $redirectTo = 'mship/notification/list';

    public function __construct(private NotificationFlowService $notificationFlowService)
    {
        parent::__construct();
    }

    public function postAcknowledge($notification)
    {
        $forcedReturnUrl = Session::has('force_notification_read_return_url')
            ? (string) Session::pull('force_notification_read_return_url')
            : null;

        $result = $this->notificationFlowService->acknowledge($this->account, $notification, $this->redirectPath(), $forcedReturnUrl);
        $redirectData = $this->notificationFlowService->buildAcknowledgeRedirect($result);

        if ($redirectData->usesRouteRedirect()) {
            return redirect()->route($redirectData->route);
        }

        return redirect((string) $redirectData->redirectUrl);
    }

    public function getList()
    {
        $allowedToLeave = $this->notificationFlowService->allowedToLeaveNotificationList(
            $this->account,
            Session::has('force_notification_read_return_url')
        );

        return $this->viewMake('mship.notification.list')
            ->with('unreadNotifications', $this->account->unreadNotifications)
            ->with('readNotifications', $this->account->readSystemNotifications)
            ->with('allowedToLeave', $allowedToLeave);
    }
}
