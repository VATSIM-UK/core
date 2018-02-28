<?php

namespace App\Events\Smartcars;

use App\Events\Event;
use App\Models\Smartcars\Bid;
use Illuminate\Queue\SerializesModels;

class BidCompleted extends Event
{
    use SerializesModels;

    public $bid;

    public function __construct(Bid $bid)
    {
        $this->bid = $bid;
    }
}
