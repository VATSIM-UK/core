<?php

namespace App\Modules\Visittransfer\Events;

use App\Events\Event;

use App\Modules\Visittransfer\Models\Reference;
use Illuminate\Queue\SerializesModels;

class ReferenceDeleted extends Event
{
    use SerializesModels;

    public $reference = null;

    public function __construct(Reference $reference)
    {
        $this->reference = $reference;
    }
}
