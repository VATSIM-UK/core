<?php

namespace App\Events\VisitTransfer;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use App\Models\VisitTransfer\Reference;

class ReferenceRejected extends Event
{
    use SerializesModels;

    public $reference = null;

    public function __construct(Reference $reference)
    {
        $this->reference = $reference;
    }
}
