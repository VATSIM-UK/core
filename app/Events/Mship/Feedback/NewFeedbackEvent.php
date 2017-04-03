<?php

namespace App\Events\Mship\Feedback;

use Illuminate\Queue\SerializesModels;
use App\Models\Mship\Feedback\Feedback;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewFeedbackEvent
{
    use InteractsWithSockets, SerializesModels;

    public $feedback;

    public function __construct(Feedback $feedback)
    {
        $this->feedback = $feedback;
    }

}
