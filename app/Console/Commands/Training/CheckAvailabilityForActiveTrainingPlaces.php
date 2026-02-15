<?php

namespace App\Console\Commands\Training;

use App\Jobs\Training\CheckAvailability;
use App\Models\Training\TrainingPlace\TrainingPlace;
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
        // training places have soft deletes, so we need to get all active training places
        $trainingPlaces = TrainingPlace::whereNull('deleted_at')->get();

        foreach ($trainingPlaces as $trainingPlace) {
            CheckAvailability::dispatch($trainingPlace);
        }
    }
}
