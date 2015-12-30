<?php

namespace App\Modules\Statistics\Jobs;

use App\Models\Mship\Qualification;
use App\Modules\Statistics\Events\StatisticsDownloaded;
use App\Modules\Statistics\Events\StatisticsParsed;
use App\Modules\Statistics\Models\Atc;
use Carbon\Carbon;
use Illuminate\Queue\SerializeModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\DispatchesJobs;

class StatisticsDownloadAndParse extends \App\Jobs\Job implements SelfHandling, ShouldQueue {

    use InteractsWithQueue, SerializesModels, DispatchesJobs;

    private $vatsimPHP = null;

    /**
     * Create a new StatisticsDownload job.
     *
     * @return void
     */
    public function __construct(){
        $this->vatsimPHP = new \Vatsimphp\VatsimData();
        $this->vatsimPHP->setConfig("forceDataRefresh", true);
    }

    /**
     * Execute the StatisticsDownload job.
     *
     * By-product of this is that the StatisticsDownload event will fire.
     * @return void
     */
    public function handle(){
        $this->vatsimPHP->loadData();
        event(new StatisticsDownloaded);

        $feedLastUpdatedAt = Carbon::now();

        $this->parseRecentDownload();

        $this->endExpiredAtcSessions($feedLastUpdatedAt);
    }

    /**
     * Parse the recently downloaded data, inserting/updating controllers in the feed as necessary.
     *
     * @return void
     */
    private function parseRecentDownload(){

        foreach($this->vatsimPHP->getControllers() as $controllerData){
            // We need to convert a VATSIM rating to a local qualification.
            $qualification = Qualification::parseVatsimATCQualification($controllerData["rating"]);

            $eloquentController = Atc::firstOrCreate([
                "account_id" => $controllerData['cid'],
                "callsign" => $controllerData['callsign'],
                "qualification_id" => $qualification->qualification_id,
                "facility_type" => $controllerData['facilitytype'],
                "deleted_at" => NULL, // Must be here as firstOrCreate doesn't honour softDeletes
            ]);

            $eloquentController = $this->setControllerConnectedAt($eloquentController, $controllerData);

            $eloquentController->touch(); // Keeping them online in the database.
        }

        event(new StatisticsParsed());
    }

    /**
     * Determine if we need to set the connected_at time (and parse it as required).
     *
     * If a none persisted eloquent model is passed to this method, it will persist it.
     *
     * @param $eloquentController Atc The persisted eloquent controller.
     * @param $controllerData Array The Array of data from the VatsimPHP feed.
     *
     * @return mixed
     */
    private function setControllerConnectedAt(Atc $eloquentController, Array $controllerData){
        if($eloquentController->connected_at == NULL){
            $eloquentController->connected_at = Carbon::createFromFormat("YmdHis", $controllerData['time_logon']);
            $eloquentController->save();
        }

        return $eloquentController;
    }

    /**
     * Update the disconnected_at flag for any controllers not in the latest data feed.
     *
     * @return void
     */
    private function endExpiredAtcSessions($feedLastUpdatedAt){
        $expiringAtc = Atc::where("updated_at", "<", $feedLastUpdatedAt)
                          ->where("disconnected_at", "IS", NULL)
                          ->get();

        foreach($expiringAtc as $atc){
            $atc->disconnected_at = $feedLastUpdatedAt;
            $atc->save();
        }
    }
}