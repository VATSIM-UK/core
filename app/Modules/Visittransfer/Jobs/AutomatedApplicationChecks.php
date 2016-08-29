<?php namespace App\Modules\Visittransfer\Jobs;

use App\Jobs\Job;
use App\Jobs\Messages\CreateNewMessage;
use App\Models\Mship\Account;
use App\Modules\Visittransfer\Models\Application;
use Bus;
use Carbon\Carbon;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use View;

class AutomatedApplicationChecks extends Job implements ShouldQueue {
    use InteractsWithQueue, SerializesModels;

    private $application = null;
    private $testsPassed = true;

    public function __construct(Application $application){
        $this->application = $application;
    }

    /**
     * Run all of the automated checks on this application, storing the results for the administrators to review.
     *
     * @return void
     */
    public function handle(){
        if(!$this->application->is_under_review){
            return true;
        }

        $this->checkCurrentRatingOver90Days();
        $this->checkCurrentRating50Hours();

        $this->application->markAsUnderReview();
    }

    private function checkCurrentRatingOver90Days(){
        return true; // TODO: This will need removing once these are implemented properly.

        $currentAtcQualification = $this->application->account->qualification_atc;
        $dateAchieved = $currentAtcQualification->pivot->created_at->startOfDay();
        $daysDifference = $this->application->created_at->startOfDay()->diffInDays($dateAchieved);

        if($daysDifference >= 90){
            return true;
        }

        $this->testsPassed = false;
    }

    private function checkCurrentRating50Hours(){
        // TODO: Figure this out.
    }
}
