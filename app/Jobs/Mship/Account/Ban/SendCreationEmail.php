<?php

namespace App\Jobs\Mship\Account\Ban;

use App\Jobs\Job;
use Illuminate\Contracts\Bus\SelfHandling;

class SendCreationEmail extends Job implements SelfHandling
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}
