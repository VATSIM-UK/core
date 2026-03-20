<?php

namespace App\Http\Controllers\NetworkData;

use App\Services\NetworkData\OnlineAtcFeedService;
use Illuminate\Routing\Controller as BaseController;

class Feed extends BaseController
{
    public function __construct(private OnlineAtcFeedService $onlineAtcFeedService) {}

    public function getOnline()
    {
        return response()->json($this->onlineAtcFeedService->getOnlineAtcSessions());
    }
}
