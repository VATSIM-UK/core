<?php

namespace App\Notifications;

use App\Models\NetworkData\Atc;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class AtcSessionRecordedConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    private $atcSession = null;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Atc $atcSession)
    {
        parent::__construct();

        $this->atcSession = $atcSession;
    }
}
