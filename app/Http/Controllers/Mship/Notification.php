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

        if ($result->status === 'already_read') {
            return redirect()->route('mship.manage.dashboard');
        }

        return redirect((string) $result->redirectUrl);
    }

    public function getList()
    {
        $allowedToLeave = ! Session::has('force_notification_read_return_url')
            || $this->notificationFlowService->canLeaveNotificationFlow($this->account);

        return $this->viewMake('mship.notification.list')
            ->with('unreadNotifications', $this->account->unreadNotifications)
            ->with('readNotifications', $this->account->readSystemNotifications)
            ->with('allowedToLeave', $allowedToLeave);
    }
}
