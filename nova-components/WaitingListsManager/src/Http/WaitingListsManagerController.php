<?php

namespace Vatsimuk\WaitingListsManager\Http;

use App\Models\Training\WaitingList;
use Illuminate\Routing\Controller;

class WaitingListsManagerController extends Controller
{
    private $waitingList;

    public function __construct(WaitingList $waitingList)
    {
        $this->waitingList = $waitingList;
    }

    public function index(WaitingList $waitingList)
    {
        return WaitingListAccountResource::collection($waitingList->accounts()->orderBy('position')->get());
    }
}
