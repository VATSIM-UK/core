<?php

namespace App\Events\Mship\Feedback;

use App\Models\Mship\Feedback\Feedback;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class NewFeedbackEvent
{
    use InteractsWithSockets, SerializesModels;

    public $feedback;

    public function __construct(Feedback $feedback)
    {
        $this->feedback = $feedback;
    }
}
