<?php

namespace App\Modules\Visittransfer\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use App\Modules\Visittransfer\Models\Reference;

class ReferenceDeleted extends Event
{
    use SerializesModels;

    public $reference = null;

    public function __construct(Reference $reference)
    {
        $this->reference = $reference;
    }
}
