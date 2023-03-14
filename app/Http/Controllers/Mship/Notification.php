<?php

namespace App\Http\Controllers\Mship;

use Auth;
use Redirect;
use Session;

class Notification extends \App\Http\Controllers\BaseController
{
    protected $redirectTo = 'mship/notification/list';

    public function postAcknowledge($notification)
    {
        if ($this->account->hasReadNotification($notification)) {
            return redirect()->route('mship.manage.dashboard');
        }
        $this->account->readSystemNotifications()
            ->attach($notification->id);

        // If this is an interrupt AND we're got no more important notifications, then let's go back!
        if (Session::has('force_notification_read_return_url')) {
            if (! Auth::user()->has_unread_important_notifications and ! Auth::user()->get_unread_must_read_notifications) {
                return Redirect::to(Session::pull('force_notification_read_return_url'));
            }
        }

        return redirect($this->redirectPath());
    }

    public function getList()
    {
        // Get all unread notifications.
        $unreadNotifications = $this->account->unreadNotifications;
        $readNotifications = $this->account->readSystemNotifications;

        return $this->viewMake('mship.notification.list')
            ->with('unreadNotifications', $unreadNotifications)
            ->with('readNotifications', $readNotifications)
            ->with('allowedToLeave', ! Session::has('force_notification_read_return_url') or (! Auth::user()->has_unread_important_notifications and ! Auth::user()->get_unread_must_read_notifications));
    }
}
