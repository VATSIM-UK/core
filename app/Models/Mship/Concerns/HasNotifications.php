<?php

namespace App\Models\Mship\Concerns;

use App\Models\Sys\Notification as SysNotification;
use Carbon\Carbon;

trait HasNotifications
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function readSystemNotifications()
    {
        return $this->belongsToMany(
            \App\Models\Sys\Notification::class,
            'sys_notification_read',
            'account_id',
            'notification_id'
        )->orderBy('status', 'DESC')
            ->orderBy('effective_at', 'DESC')
            ->withPivot(['created_at']);
    }

    /**
     * @param  Notification  $notification
     * @return bool
     */
    public function hasReadNotification(SysNotification $notification)
    {
        return $this->readSystemNotifications()->wherePivot('notification_id', $notification->id)->exists();
    }

    public function getUnreadNotificationsAttribute()
    {
        // Get all read notifications
        $readNotifications = $this->readSystemNotifications;

        // Get all notifications
        $allNotifications = SysNotification::published()
            ->orderBy('status', 'DESC')
            ->orderBy('effective_at', 'DESC')
            ->get();

        // The difference between the two MUST be the ones that are unread, right?
        return $allNotifications->diff($readNotifications);
    }

    public function getUnreadMustAcknowledgeNotificationsAttribute()
    {
        return $this->unread_notifications->filter(function ($notification) {
            return $notification->status === SysNotification::STATUS_MUST_ACKNOWLEDGE;
        });
    }

    public function getHasUnreadNotificationsAttribute()
    {
        return $this->unreadNotifications->count() > 0;
    }

    public function getHasUnreadImportantNotificationsAttribute()
    {
        $unreadNotifications = $this->unreadNotifications->filter(function ($notice) {
            return $notice->status == SysNotification::STATUS_IMPORTANT;
        });

        return $unreadNotifications->count() > 0;
    }

    public function getHasUnreadMustAcknowledgeNotificationsAttribute()
    {
        $unreadNotifications = $this->unreadNotifications->filter(function ($notice) {
            return $notice->status == SysNotification::STATUS_MUST_ACKNOWLEDGE;
        });

        return $unreadNotifications->count() > 0;
    }

    /**
     * Calculates the amount of time that has lapsed since the notification became effective.
     *
     * @return int Period the notification has been effective for, in hours.
     */
    public function getUnreadMustAcknowledgeTimeElapsedAttribute()
    {
        if ($this->has_unread_must_acknowledge_notifications) {
            return $this->unread_must_acknowledge_notifications
                ->sortBy('effective_at')
                ->first()
                ->effective_at
                ->diffInHours(Carbon::now(), true);
        } else {
            return 0;
        }
    }
}
