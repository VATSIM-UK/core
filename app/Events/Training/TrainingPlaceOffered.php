<?php

namespace App\Events\Training;

use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

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

    public function getTrainingPlaceOffer(): TrainingPlaceOffer
    {
        return $this->offer;
    }
}
