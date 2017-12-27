<?php

namespace App\Listeners\VisitTransfer;

use DB;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\VisitTransfer\ReferenceUnderReview;

class CancelPlannedRefereeNotifications implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\VisitTransfer\ReferenceUnderReview  $event
     * @return void
     */
    public function handle(ReferenceUnderReview $event)
    {
      $jobs = DB::table('jobs_pending')->where('queue', 'notifications')->get(['id', 'payload']);
      if($jobs->count() == 0){
        // No matches anyway
        return;
      }
      foreach ($jobs as $job) {
          $payload = json_decode($job->payload);
          if($payload->displayName != \App\Notifications\ApplicationReferenceRequest::class){
            continue;
          }

          $reference = unserialize($payload->data->command)->notifiables;

          if($event->reference != $reference){
            return;
          }

          DB::table('jobs_pending')->where('id', $job->id)->delete();
      }
      return;
    }
}
