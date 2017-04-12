<?php

namespace App\Modules\Visittransfer\Notifications;

use Illuminate\Bus\Queueable;
use App\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Visittransfer\Models\Reference;
use Illuminate\Notifications\Messages\MailMessage;

class ApplicationReferenceRequest extends Notification implements ShouldQueue
{
    use Queueable;

    private $reference;
    private $application;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Reference $reference)
    {
        parent::__construct();

        $this->reference = $reference;
        $this->application = $reference->application;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if (!$this->reference->is_requested) {
            return []; // Already been completed
        } else {
            return ['mail', 'database'];
        }
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $subject = "[{$this->application->public_id}] {$this->application->type_string} Reference Request";

        return (new MailMessage)
            ->from('community@vatsim-uk.co.uk', 'VATSIM UK - Community Department')
            ->subject($subject)
            ->view('visittransfer::emails.reference.request', [
                'recipient' => $this->reference->account,
                'subject' => $subject,
                'reference' => $this->reference,
                'application' => $this->application,
                'token' => $this->reference->token,
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
