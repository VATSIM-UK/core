<?php

namespace App\Listeners\Mship;

use App\Notifications\Contracts\HasEmailType;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Log;

class CheckEmailPreferences
{
    public function handle(NotificationSending $event): bool
    {
        $notification = $event->notification;
        $notifiable = $event->notifiable;

        if (! ($notification instanceof HasEmailType)) {
            return true;
        }

        if (! method_exists($notifiable, 'isEmailEnabled')) {
            return true;
        }

        $emailType = $notification->getEmailType();

        if (! $notifiable->isEmailEnabled($emailType)) {
            $notifiableId = method_exists($notifiable, 'getKey') ? $notifiable->getKey() : '?';
            Log::info("Email suppressed for account {$notifiableId}: {$emailType->value} ({$emailType->label()})");

            return false;
        }

        return true;
    }
}
