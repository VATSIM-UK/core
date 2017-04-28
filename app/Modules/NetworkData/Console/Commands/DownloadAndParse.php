<?php

namespace App\Modules\NetworkData\Console\Commands;

use Carbon\Carbon;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Modules\NetworkData\Models\Atc;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Modules\NetworkData\Events\NetworkDataParsed;
use App\Modules\NetworkData\Events\NetworkDataDownloaded;

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
    private $lastUpdatedAt = null;
    private $controllerTotalCount = 0;
    private $controllerAcceptedCount = 0;
    private $controllerExpiredCount = 0;

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
        $this->info('Loading VatsimPHP Data.', 'v');

        $this->vatsimPHP->loadData();

        $this->info('Downloaded data.', 'v');

        $this->setLastUpdatedTimestamp();

        $this->info('Last updated at '.$this->lastUpdatedAt->toDateTimeString(), 'v');

        event(new NetworkDataDownloaded());

        $this->parseRecentDownload();

        $this->endExpiredAtcSessions();

        Atc::flushCache();

        $this->sendSlackSuccess('Completed Successfully', [
            'Controllers Total' => $this->controllerTotalCount,
            'Controllers Accepted' => $this->controllerTotalCount,
            'Controllers Expired' => $this->controllerAcceptedCount,
        ]);
    }

    /**
     * Set the last updated timestamp to something useable (i.e the value from the top of the file).
     */
    private function setLastUpdatedTimestamp()
    {
        $gi = $this->vatsimPHP->getGeneralInfo()->toArray();

        if ($this->verbosity >= 3) {
            $this->info('General header details:', 'vvv');
            foreach ($gi as $key => $value) {
                $this->info("\t".str_pad($key, 20, ' ', STR_PAD_RIGHT).' = '.$value, 'vvv');
            }
        }

        $this->lastUpdatedAt = Carbon::createFromTimestampUTC($gi['update']);
    }

    /*
    **
    * Parse the recently downloaded data, inserting/updating controllers in the feed as necessary.
    *
    * @return void
    */
    private function parseRecentDownload()
    {
        $this->info('Handling controller details.', 'v');

        foreach ($this->vatsimPHP->getControllers() as $controllerData) {
            $this->controllerTotalCount++;

            $this->info("\tController #".$this->controllerTotalCount.' - '.str_pad($controllerData['callsign'],
                    10, ' ', STR_PAD_RIGHT).':', 'vvv');

            if ($controllerData['facilitytype'] < 1 || substr($controllerData['callsign'], -4) == '_OBS') {
                $this->info("\t\tFacility type - Observer.  Ignoring", 'vvv');
                continue;
            }

            if (substr($controllerData['callsign'], -4) == '_SUP') {
                $this->info("\t\tFacility type - Supervisor.  Ignoring", 'vvv');
                continue;
            }

            if (substr($controllerData['callsign'], -5) == '_ATIS') {
                $this->info("\t\tFacility type - ATIS.  Ignoring", 'vvv');
                continue;
            }

            if ($controllerData['frequency'] < 118 || $controllerData['frequency'] > 136) {
                $this->info("\t\tFrequency isn't in range.  Ignoring.", 'vvv');
                continue;
            }

            $qualification = Qualification::parseVatsimATCQualification($controllerData['rating']);
            $this->info("\t\tQualification processed as ".$qualification, 'vvv');

            $account = Account::findOrRetrieve($controllerData['cid']);
            $this->info("\t\tAccount loaded: ".$account->id.' - '.$account->name, 'vvv');

            $atcSession = Atc::updateOrCreate(
                [
                    'account_id' => $account->id,
                    'callsign' => $controllerData['callsign'],
                    'frequency' => $controllerData['frequency'],
                    'qualification_id' => is_null($qualification) ? 0 : $qualification->id,
                    'facility_type' => $controllerData['facilitytype'],
                    'connected_at' => Carbon::createFromFormat('YmdHis', $controllerData['time_logon']),
                    'disconnected_at' => null,
                    'deleted_at' => null,
                ],
                [
                    'updated_at' => Carbon::now(),
                ]
            );

            if ($atcSession->wasRecentlyCreated) {
                $this->info("\t\tNew session - created.", 'vvv');
            } else {
                $this->info("\t\tExisting session - updated.", 'vvv');
            }

            $this->controllerAcceptedCount++;
        }

        event(new NetworkDataParsed());

        $this->info('Controller details parsed', 'v');
        $this->info("\tTotal controllers: ".$this->controllerTotalCount, 'v');
        $this->info("\tAccepted controllers: ".$this->controllerAcceptedCount, 'v');
        $this->info('');
    }

    /**
     * Update the disconnected_at flag for any controllers not in the latest data feed.
     *
     * @return void
     */
    private function endExpiredAtcSessions()
    {
        $this->info('Expiring old ATC sessions.', 'v');

        $expiringAtc = Atc::online()
                          ->where('updated_at', '<', $this->lastUpdatedAt)
                          ->get();

        $expiringAtc->each(function ($session) {
            $this->info("\t".$session->callsign.':', 'vvv');

            $this->info("\t\tConnect at: ".$session->created_at, 'vvv');
            $this->info("\t\tUpdated at: ".$session->updated_at, 'vvv');

            $session->disconnectAt($this->lastUpdatedAt);

            $this->info("\t\tExpired.", 'vvv');
            $this->controllerExpiredCount++;
        });

        $this->info('Stale controller sessions expired', 'v');
        $this->info("\tExpired controllers: ".$this->controllerExpiredCount, 'v');
    }
}
