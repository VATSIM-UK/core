<?php

namespace App\Notifications;

use App\Models\VisitTransfer\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\Messages\MailMessage;

class ApplicationStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    private $application;

    private ?array $withdrawal = null;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Application $application)
    {
        parent::__construct();

        if ($application->is_withdrawn) {
            $this->withdrawal = [
                'public_id' => $application->public_id,
                'type' => $application->type_string,
                'facility_name' => $application->facility?->name,
            ];
            $this->afterCommit();
        } else {
            $this->application = $application;
        }
    }

    public function restoreModel($value)
    {
        try {
            return parent::restoreModel($value);
        } catch (ModelNotFoundException $exception) {
            if ($value->class !== Application::class || $exception->getModel() !== Application::class) {
                throw $exception;
            }

            return null;
        }
    }

    public function shouldSend($notifiable, string $channel): bool
    {
        return $this->withdrawal !== null || $this->application !== null;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $subject = $this->withdrawal !== null
            ? "[{$this->withdrawal['public_id']}] {$this->withdrawal['type']} Application Withdrawn"
            : "[{$this->application->public_id}] {$this->application->type_string} Application {$this->application->status_string}";

        return (new MailMessage)
            ->from('community@vatsim.uk', 'VATSIM UK - Community Department')
            ->subject($subject)
            ->view('visit-transfer.emails.applicant.status_changed', [
                'application' => $this->application,
                'withdrawal' => $this->withdrawal,
                'subject' => $subject,
                'recipient' => $notifiable,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [];
    }
}
