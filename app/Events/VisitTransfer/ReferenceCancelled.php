<?php

namespace App\Events\VisitTransferLegacy;

use App\Models\VisitTransferLegacy\Reference;
use Illuminate\Queue\SerializesModels;

class ReferenceCancelled
{
    use SerializesModels;

    public $reference = null;

    public function __construct(Reference $reference)
    {
        $this->reference = $reference;
    }
}
