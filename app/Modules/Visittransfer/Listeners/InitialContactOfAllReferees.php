<?php namespace App\Modules\Visittransfer\Listeners;

use App\Modules\Visittransfer\Events\ApplicationSubmitted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class InitialContactOfAllReferees implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ApplicationSubmitted $event)
    {

    }
}