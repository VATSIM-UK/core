<?php

namespace App\Events\VisitTransfer;

use App\Events\Event;
use App\Models\VisitTransfer\Reference;
use Illuminate\Queue\SerializesModels;

class ReferenceRejected extends Event
{
    use SerializesModels;

    public $reference = null;

    public function __construct(Reference $reference)
    {
        $this->reference = $reference;
    }
}
