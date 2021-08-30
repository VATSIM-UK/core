<?php

namespace App\Events\Training;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\Training\TrainingPlace\TrainingPlaceOffer;

class TrainingPlaceOffered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $offer;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(TrainingPlaceOffer $offer)
    {
        $this->offer = $offer;
    }

    public function getTrainingPlaceOffer() : TrainingPlaceOffer
    {
        return $this->offer;
    }
}
