<?php

namespace App\Events\VisitTransferLegacy;

use App\Events\Event;
use App\Models\VisitTransferLegacy\Reference;
use Illuminate\Queue\SerializesModels;

class ReferenceUnderReview extends Event
{
    use SerializesModels;

    public $reference = null;

    public function __construct(Reference $reference)
    {
        $this->reference = $reference;
    }
}
