<?php

namespace App\Events\VisitTransfer;

use Illuminate\Queue\SerializesModels;
use App\Models\VisitTransfer\Reference;

class ReferenceCancelled
{
    use SerializesModels;

    public $reference = null;

    public function __construct(Reference $reference)
    {
        $this->reference = $reference;
    }
}
