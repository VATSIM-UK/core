<?php

namespace App\Notifications;

use App\Models\VisitTransferLegacy\Reference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ApplicationReferenceAccepted extends Notification implements ShouldQueue
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
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $subject = "[{$this->application->public_id}] Reference from '{$this->reference->account->name}' Accepted";

        return (new MailMessage)
            ->from('community@vatsim.uk', 'VATSIM UK - Community Department')
            ->subject($subject)
            ->view('visit-transfer.emails.applicant.reference_accepted', ['recipient' => $notifiable, 'subject' => $subject, 'reference' => $this->reference, 'application' => $this->application]);
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
