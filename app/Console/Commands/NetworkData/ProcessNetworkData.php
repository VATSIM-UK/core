<?php

namespace App\Console\Commands\NetworkData;

use App\Console\Commands\Command;
use App\Events\NetworkData\NetworkDataDownloaded;
use App\Events\NetworkData\NetworkDataParsed;
use App\Exceptions\Mship\InvalidCIDException;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\NetworkData\Atc;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Vatsimphp\VatsimData;

class ProcessNetworkData extends Command
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

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->vatsimPHP = new VatsimData();
        $this->vatsimPHP->setConfig('forceDataRefresh', true);
    }

    /**
     * Executes all necessary console commands.
     */
    public function fire()
    {
        $this->vatsimPHP->loadData();
        $this->setLastUpdatedTimestamp();
        event(new NetworkDataDownloaded());
        $this->parseAtc();
        $this->endExpiredAtcSessions();
        event(new NetworkDataParsed());
        Atc::flushCache();
    }

    /**
     * Set the last updated timestamp to something useable (i.e the value from the top of the file).
     */
    private function setLastUpdatedTimestamp()
    {
        $generalInfo = $this->vatsimPHP->getGeneralInfo()->toArray();
        $this->lastUpdatedAt = Carbon::createFromTimestampUTC($generalInfo['update']);
    }

    /**
     * Parse the recently downloaded data, inserting/updating controllers in the feed as necessary.
     */
    private function parseAtc()
    {
        $this->info('Handling controller details.', 'v');

        foreach ($this->vatsimPHP->getControllers() as $controllerData) {
            if ($controllerData['facilitytype'] < 1 || substr($controllerData['callsign'], -4) == '_OBS') {
                // ignore observers
                continue;
            } else if (substr($controllerData['callsign'], -4) == '_SUP') {
                // ignore supervisors
                continue;
            } else if (substr($controllerData['callsign'], -5) == '_ATIS') {
                // ignore ATIS connections
                continue;
            } else if ($controllerData['frequency'] < 118 || $controllerData['frequency'] > 136) {
                // ignore out-of-range frequencies
                continue;
            }

            try {
                $account = Account::findOrRetrieve($controllerData['cid']);
            } catch (InvalidCIDException $e) {
                $this->info('Invalid CID: ' . $controllerData['cid'], 'vvv');
                continue;
            }

            $qualification = Qualification::parseVatsimATCQualification($controllerData['rating']);
            Atc::updateOrCreate(
                [
                    'account_id' => $account->id,
                    'callsign' => $controllerData['callsign'],
                    'frequency' => $controllerData['frequency'],
                    'qualification_id' => is_null($qualification) ? 0 : $qualification->id,
                    'facility_type' => $controllerData['facilitytype'],
                    'connected_at' => Carbon::createFromFormat('YmdHis', $controllerData['time_logon']),
                    'disconnected_at' => null,
                    'deleted_at' => null,
                ], [
                    'updated_at' => Carbon::now(),
                ]
            );
        }
    }

    /**
     * Update the disconnected_at flag for any controllers not in the latest data feed.
     */
    private function endExpiredAtcSessions()
    {
        $expiringAtc = Atc::online()
            ->where('updated_at', '<', $this->lastUpdatedAt)
            ->get();

        $expiringAtc->each(function (Atc $session) {
            $session->disconnectAt($this->lastUpdatedAt);
        });
    }
}
