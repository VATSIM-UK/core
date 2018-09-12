<?php

namespace Vatsimuk\WaitingListsManager;

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
        return $waitingList->accounts()->get();
    }
}
