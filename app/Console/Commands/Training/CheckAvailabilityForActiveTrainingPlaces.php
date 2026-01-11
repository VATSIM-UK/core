<?php

namespace App\Console\Commands\Training;

use Illuminate\Console\Command;

class CheckAvailabilityForActiveTrainingPlaces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'training-places:check-availability';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the availability of all training places';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
