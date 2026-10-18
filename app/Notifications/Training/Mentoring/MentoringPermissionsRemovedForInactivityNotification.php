<?php

declare(strict_types=1);

namespace App\Notifications\Training\Mentoring;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MentoringPermissionsRemovedForInactivityNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private string $category) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('Mentoring Permissions Revoked')
            ->view('emails.training.mentoring.mentoring_permissions_removed', [
                'recipient' => $notifiable,
                'category' => $this->category,
            ]);
    }
}
