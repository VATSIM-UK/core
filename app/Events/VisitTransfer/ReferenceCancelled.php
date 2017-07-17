<?php

namespace App\Events\VisitTransfer;

use App\Models\VisitTransfer\Reference;
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
