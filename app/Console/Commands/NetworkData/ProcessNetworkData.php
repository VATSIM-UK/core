<?php

namespace App\Console\Commands\NetworkData;

use App\Console\Commands\Command;
use App\Events\NetworkData\NetworkDataDownloaded;
use App\Events\NetworkData\NetworkDataParsed;
use App\Exceptions\Mship\InvalidCIDException;
use App\Models\Airport;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\NetworkData\Atc;
use App\Models\NetworkData\Pilot;
use Cache;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Collection;
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
    public function handle()
    {
        $this->vatsimPHP->loadData();
        $this->setLastUpdatedTimestamp();
        event(new NetworkDataDownloaded());
        $this->processATC();
        $this->processPilots();
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
    private function processATC()
    {
        $awaitingUpdate = Atc::online()->get()->keyBy('id');

        foreach ($this->vatsimPHP->getControllers() as $controllerData) {
            if ($controllerData['facilitytype'] < 1 || substr($controllerData['callsign'], -4) == '_OBS') {
                // ignore observers
                continue;
            } elseif (substr($controllerData['callsign'], -4) == '_SUP') {
                // ignore supervisors
                continue;
            } elseif (substr($controllerData['callsign'], -5) == '_ATIS') {
                // ignore ATIS connections
                continue;
            } elseif ($controllerData['frequency'] < 118 || $controllerData['frequency'] > 136) {
                // ignore out-of-range frequencies
                continue;
            }

            DB::beginTransaction();

            try {
                $account = Account::findOrRetrieve($controllerData['cid']);
            } catch (InvalidCIDException $e) {
                $this->info('Invalid CID: '.$controllerData['cid'], 'vvv');
                DB::commit();
                continue;
            }

            $qualification = Qualification::parseVatsimATCQualification($controllerData['rating']);
            $atc = Atc::updateOrCreate(
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

            $awaitingUpdate->forget([$atc->id]);

            DB::commit();
        }

        $this->endExpiredAtcSessions($awaitingUpdate);
    }

    /**
     * Update the disconnected_at flag for any controllers not in the latest data feed.
     *
     * @param Collection $expiringAtc
     */
    private function endExpiredAtcSessions($expiringAtc)
    {
        $expiringAtc->each(function (Atc $session) {
            $session->disconnectAt($this->lastUpdatedAt);
        });
    }

    /**
     * Parse the recently downloaded data, inserting/updating pilots in the feed as necessary.
     */
    private function processPilots()
    {
        $awaitingUpdate = Pilot::online()->get()->keyBy('id');

        foreach ($this->vatsimPHP->getPilots() as $pilotData) {
            if (empty($pilotData['planned_revision'])) {
                // ignore flights with no flightplan
                continue;
            }

            DB::beginTransaction();

            try {
                $account = Account::findOrRetrieve($pilotData['cid']);
            } catch (InvalidCIDException $e) {
                $this->info('Invalid CID: '.$pilotData['cid'], 'vvv');
                DB::commit();
                continue;
            }

            $flight = Pilot::firstOrNew([
                'account_id' => $account->id,
                'callsign' => $pilotData['callsign'],
                'flight_type' => $pilotData['planned_flighttype'],
                'departure_airport' => $pilotData['planned_depairport'],
                'arrival_airport' => $pilotData['planned_destairport'],
                'connected_at' => Carbon::createFromFormat('YmdHis', $pilotData['time_logon']),
                'disconnected_at' => null,
            ]);

            $flight->fill([
                'alternative_airport' => $pilotData['planned_altairport'],
                'aircraft' => $pilotData['planned_aircraft'],
                'cruise_altitude' => $pilotData['planned_altitude'],
                'cruise_tas' => $pilotData['planned_tascruise'],
                'route' => $pilotData['planned_route'],
                'remarks' => $pilotData['planned_remarks'],
            ]);

            if ($flight->exists) {
                $departureAirport = $this->getAirport($flight->departure_airport);
                $arrivalAirport = $this->getAirport($flight->arrival_airport);
                $alternativeAirport = $this->getAirport($flight->alternative_airport);

                // check their location before we update it
                $wasAtDepartureAirport = $flight->isAtAirport($departureAirport);
                $wasAtArrivalAirport = $flight->isAtAirport($arrivalAirport);
                $wasAtAlternativeAirport = $flight->isAtAirport($alternativeAirport);

                // update their location
                $flight->current_latitude = !empty($pilotData['latitude']) ? $pilotData['latitude'] : null;
                $flight->current_longitude = !empty($pilotData['longitude']) ? $pilotData['longitude'] : null;
                $flight->current_altitude = !empty($pilotData['altitude']) ? $pilotData['altitude'] : null;
                $flight->current_groundspeed = !empty($pilotData['groundspeed']) ? $pilotData['groundspeed'] : null;

                // check their new location
                $isAtDepartureAirport = $flight->isAtAirport($departureAirport);
                $isAtArrivalAirport = $flight->isAtAirport($arrivalAirport);
                $isAtAlternativeAirport = $flight->isAtAirport($alternativeAirport);

                // determine if they have departed or arrived at their planned airports
                $departed = $wasAtDepartureAirport && !$isAtDepartureAirport;
                if ($departed) {
                    $flight->departed_at = $this->lastUpdatedAt;
                }

                $arrivedAtMainAirport = !$wasAtArrivalAirport && $isAtArrivalAirport;
                $arrivedAtAlternativeAirport = !$wasAtAlternativeAirport && $isAtAlternativeAirport;
                if ($arrivedAtMainAirport || $arrivedAtAlternativeAirport) {
                    $flight->arrived_at = $this->lastUpdatedAt;
                }
            } else {
                // pilot just connected
                $flight->current_latitude = !empty($pilotData['latitude']) ? $pilotData['latitude'] : null;
                $flight->current_longitude = !empty($pilotData['longitude']) ? $pilotData['longitude'] : null;
                $flight->current_altitude = !empty($pilotData['altitude']) ? $pilotData['altitude'] : null;
                $flight->current_groundspeed = !empty($pilotData['groundspeed']) ? $pilotData['groundspeed'] : null;
            }

            $flight->touch();

            $awaitingUpdate->forget([$flight->id]);

            DB::commit();
        }

        $this->endExpiredPilotSessions($awaitingUpdate);
    }

    /**
     * Update the disconnected_at flag for any pilots not in the latest data feed.
     *
     * @param Collection $expiringPilots
     */
    private function endExpiredPilotSessions($expiringPilots)
    {
        $expiringPilots->each(function (Pilot $session) {
            $session->disconnected_at = $this->lastUpdatedAt;
            $session->save();
        });
    }

    /**
     * Retrieve and cache an airport from the database.
     *
     * @param $ident string
     * @return mixed
     */
    private function getAirport(string $ident)
    {
        return Cache::remember('airport_'.$ident, 720, function () use ($ident) {
            return Airport::where('ident', $ident)->first();
        });
    }
}
