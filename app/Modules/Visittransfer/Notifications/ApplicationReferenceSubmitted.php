<?php

namespace App\Modules\Visittransfer\Notifications;

use App\Models\Mship\Account;
use Illuminate\Bus\Queueable;
use App\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Visittransfer\Models\Reference;
use Illuminate\Notifications\Messages\MailMessage;

class ApplicationReferenceSubmitted extends Notification implements ShouldQueue
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
        if ($notifiable instanceof Account) {
            $subject = "[{$this->application->public_id}] Reference from '{$this->reference->account->name}' Submitted";

            return (new MailMessage)
                ->from('community@vatsim-uk.co.uk', 'VATSIM UK - Community Department')
                ->subject($subject)
                ->view('visittransfer::emails.applicant.reference_submitted', [
                    'reference' => $this->reference,
                    'application' => $this->application,
                    'recipient' => $notifiable,
                    'subject' => $subject,
                ]);
        } elseif ($notifiable instanceof Reference) {
            $subject = "[{$this->application->public_id}] Thank You for Your Reference";

            return (new MailMessage)
                ->from('community@vatsim-uk.co.uk', 'VATSIM UK - Community Department')
                ->subject($subject)
                ->view('visittransfer::emails.reference.reference_submitted', [
                    'reference' => $this->reference,
                    'application' => $this->application,
                    'recipient' => $this->reference->account,
                    'subject' => $subject,
                ]);
        }
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
