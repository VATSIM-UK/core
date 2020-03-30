<?php

namespace App\Console\Commands\Smartcars;

use App\Console\Commands\Command;
use App\Events\Smartcars\BidCompleted;
use App\Models\Smartcars\Bid;

class ReprocessBids extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Smartcars:Reprocess';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reprocesses all FTE Bids.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $bids = Bid::all();

        foreach($bids as $bid)
        {
            event(new BidCompleted($bid));
        }
    }
}
