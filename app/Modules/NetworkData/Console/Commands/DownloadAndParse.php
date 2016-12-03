<?php

namespace App\Modules\NetworkData\Console\Commands;

use App\Models\Mship\Qualification;
use App\Modules\NetworkData\Events\NetworkDataDownloaded;
use App\Modules\NetworkData\Events\NetworkDataParsed;
use App\Modules\NetworkData\Models\Atc;
use Illuminate\Foundation\Bus\DispatchesJobs;

class DownloadAndParse extends \App\Console\Commands\Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'networkdata:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download and parse the VATSIM data feed file.';

    private $vatsimPHP = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->vatsimPHP = new \Vatsimphp\VatsimData();
        $this->vatsimPHP->setConfig('forceDataRefresh', true);
    }

    /**
     * Executes all necessary console commands.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->vatsimPHP->loadData();
        event(new NetworkDataDownloaded());

        $feedLastUpdatedAt = \Cache::pull('networkdata_last_update_of_data', \Carbon\Carbon::now());

        $this->parseRecentDownload();

        print_r($feedLastUpdatedAt);

        $this->endExpiredAtcSessions($feedLastUpdatedAt);
    }

    /*
    **
    * Parse the recently downloaded data, inserting/updating controllers in the feed as necessary.
    *
    * @return void
    */
    private function parseRecentDownload()
    {
        foreach ($this->vatsimPHP->getControllers() as $controllerData) {
            $qualification = Qualification::parseVatsimATCQualification($controllerData['rating']);

            Atc::updateOrCreate(
                [
                    'account_id'       => $controllerData['cid'],
                    'callsign'         => $controllerData['callsign'],
                    'qualification_id' => is_null($qualification) ? 0 : $qualification->id,
                    'facility_type'    => $controllerData['facilitytype'],
                    'connected_at'     => Carbon::createFromFormat('YmdHis', $controllerData['time_logon']),
                    'disconnected_at'  => null,
                    'deleted_at'       => null,
                ],
                [
                    'updated_at' => \Carbon\Carbon::now(),
                ]
            );
        }

        event(new NetworkDataParsed());

        \Cache::put('networkdata_last_update_of_data', \Carbon\Carbon::now(), 60 * 60 * 24 * 7);
    }

    /**
     * Determine if we need to set the connected_at time (and parse it as required).
     *
     * If a none persisted eloquent model is passed to this method, it will persist it.
     *
     * @param $eloquentController Atc The persisted eloquent controller.
     * @param $controllerData     Array The Array of data from the VatsimPHP feed.
     *
     * @return mixed
     */
    private function setControllerConnectedAt(Atc $eloquentController, array $controllerData)
    {
        if ($eloquentController->connected_at == null) {
            $eloquentController->connected_at = Carbon::createFromFormat('YmdHis', $controllerData['time_logon']);
            $eloquentController->save();
        }

        return $eloquentController;
    }

    /**
     * Update the disconnected_at flag for any controllers not in the latest data feed.
     *
     * @return void
     */
    private function endExpiredAtcSessions($feedLastUpdatedAt)
    {
        $expiringAtc = Atc::where('updated_at', '<', $feedLastUpdatedAt)
                          ->whereNull('disconnected_at');

        foreach ($expiringAtc as $atc) {
            $atc->disconnected_at = $feedLastUpdatedAt;
            $atc->save();
        }
    }
}
