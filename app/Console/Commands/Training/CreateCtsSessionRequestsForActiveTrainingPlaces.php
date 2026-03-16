<?php

declare(strict_types=1);

namespace App\Console\Commands\Training;

use App\Jobs\Training\CreateCtsSessionRequestForTrainingPlace;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Illuminate\Console\Command;

class CreateCtsSessionRequestsForActiveTrainingPlaces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'training-places:create-cts-session-requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create CTS session requests for all active training places';

    public function handle(): int
    {
        $trainingPlaces = TrainingPlace::whereNull('deleted_at')->get();

        foreach ($trainingPlaces as $trainingPlace) {
            CreateCtsSessionRequestForTrainingPlace::dispatch($trainingPlace);
        }

        return self::SUCCESS;
    }
}
