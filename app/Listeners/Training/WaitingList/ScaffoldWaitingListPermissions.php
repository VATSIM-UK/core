<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Training\WaitingListCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\Permission\Models\Permission;

class ScaffoldWaitingListPermissions
{
    /**
     * Handle the event.
     *
     * @param  WaitingListCreated  $event
     * @return void
     */
    public function handle(WaitingListCreated $event)
    {
        $event->waitingList->generateTemplatePermissions()->each(function ($item) {
            Permission::create([
                'name' => $item,
                'guard_name' => 'web'
            ]);
        });
    }
}
