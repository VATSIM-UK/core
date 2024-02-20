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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

    private $lastUpdatedAt = null;

    private $networkData = null;

    /**
     * Executes all necessary console commands.
     */
    public function handle()
    {
        $this->networkData = Http::get(config('vatsim-data-feed.base'));
        $this->info('Getting network data from VATSIM.');

        if ($this->networkData->failed() || ! $this->networkData->json()) {
            Log::error("Unable to process VATSIM network data. Status {$this->networkData->status()}");

            return;
        }

        $this->setLastUpdatedTimestamp();
        event(new NetworkDataDownloaded());
        $this->processATC();
        // $this->processPilots();
        event(new NetworkDataParsed());

        $this->info('Network data updated.');
    }

    /**
     * Set the last updated timestamp to something useable (i.e the value from the top of the file).
     */
    private function setLastUpdatedTimestamp()
    {
        $generalInfo = $this->networkData->json('general');
        $this->lastUpdatedAt = Carbon::create($generalInfo['update_timestamp']);
        $this->info('Network data obtained.');
    }

    /**
     * Parse the recently downloaded data, inserting/updating controllers in the feed as necessary.
     */
    private function processATC()
    {
        $this->info('Processing ATC connections...');

        $controllers = $this->networkData->json('controllers');

        $progressBar = $this->output->createProgressBar(count($controllers));

        $awaitingUpdate = Atc::online()->get()->keyBy('id');

        $progressBar->start();

        foreach ($controllers as $controller) {
            if ($controller['facility'] < 1 || substr($controller['callsign'], -4) == '_OBS') {
                // ignore observers
                continue;
            } elseif (substr($controller['callsign'], -4) == '_SUP') {
                // ignore supervisors
                continue;
            } elseif (substr($controller['callsign'], -5) == '_ATIS') {
                // ignore ATIS connections
                continue;
            } elseif ($controller['frequency'] < 118 || $controller['frequency'] > 137) {
                // ignore out-of-range frequencies
                continue;
            }

            DB::beginTransaction();

            try {
                $account = Account::findOrRetrieve($controller['cid']);
            } catch (InvalidCIDException $e) {
                $this->info('Invalid CID: '.$controller['cid'], 'vvv');
                DB::commit();

                continue;
            }

            if (! $account) {
                $this->info('Unable to find or retrieve CID: '.$controller['cid'], 'vvv');

                continue;
            }

            $qualification = Qualification::parseVatsimATCQualification($controller['rating']);
            $atc = Atc::updateOrCreate(
                [
                    'account_id' => $account->id,
                    'callsign' => $controller['callsign'],
                    'frequency' => $controller['frequency'],
                    'qualification_id' => is_null($qualification) ? 0 : $qualification->id,
                    'facility_type' => $controller['facility'],
                    'connected_at' => Carbon::create($controller['logon_time']),
                    'disconnected_at' => null,
                    'deleted_at' => null,
                ],
                [
                    'updated_at' => Carbon::now(),
                ]
            );

            $awaitingUpdate->forget([$atc->id]);

            DB::commit();

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        if ($awaitingUpdate->isNotEmpty()) {
            $this->endExpiredAtcSessions($awaitingUpdate);
        }
    }

    /**
     * Update the disconnected_at flag for any controllers not in the latest data feed.
     *
     * @param  Collection  $expiringAtc
     */
    private function endExpiredAtcSessions($expiringAtc)
    {
        $this->info('Ending expired ATC sessions.');

        $progressBar = $this->output->createProgressBar(count($expiringAtc));
        $progressBar->start();

        $expiringAtc->each(function (Atc $session) use ($progressBar) {
            $session->disconnectAt($this->lastUpdatedAt);
            $progressBar->advance();
        });

        $progressBar->finish();
        $this->newLine();
    }

    /**
     * Parse the recently downloaded data, inserting/updating pilots in the feed as necessary.
     */
    private function processPilots()
    {
        $this->info('Processing Pilot connections...');

        $pilots = $this->networkData->json('pilots');

        $progressBar = $this->output->createProgressBar(count($pilots));

        $awaitingUpdate = Pilot::online()->get()->keyBy('id');

        $progressBar->start();

        foreach ($pilots as $pilot) {
            if (empty($pilot['flight_plan'])) {
                // ignore flights with no flightplan
                continue;
            }

            DB::beginTransaction();

            try {
                $account = Account::findOrRetrieve($pilot['cid']);
            } catch (InvalidCIDException $e) {
                $this->info('Invalid CID: '.$pilot['cid'], 'vvv');
                DB::commit();

                continue;
            }

            if (! $account) {
                $this->info('Unable to find or retrieve CID: '.$pilot['cid'], 'vvv');

                continue;
            }

            $flight = Pilot::firstOrNew([
                'account_id' => $account->id,
                'callsign' => $pilot['callsign'],
                'flight_type' => $pilot['flight_plan']['flight_rules'],
                'departure_airport' => $pilot['flight_plan']['departure'],
                'arrival_airport' => $pilot['flight_plan']['arrival'],
                'connected_at' => Carbon::create($pilot['logon_time']),
                'disconnected_at' => null,
            ]);

            $flight->fill([
                'alternative_airport' => $pilot['flight_plan']['alternate'],
                'aircraft' => $pilot['flight_plan']['aircraft'],
                'cruise_altitude' => $pilot['flight_plan']['altitude'],
                'cruise_tas' => $pilot['flight_plan']['cruise_tas'],
                'route' => $pilot['flight_plan']['route'],
                'remarks' => $pilot['flight_plan']['remarks'],
            ]);

            if ($pilot['latitude'] > 90 || $pilot['latitude'] < -90) {
                $pilot['latitude'] = null;
            }
            if ($pilot['longitude'] > 180 || $pilot['longitude'] < -180) {
                $pilot['longitude'] = null;
            }

            if ($flight->exists) {
                $departureAirport = $this->getAirport($flight->departure_airport);
                $arrivalAirport = $this->getAirport($flight->arrival_airport);
                $alternativeAirport = $this->getAirport($flight->alternative_airport);

                // check their location before we update it
                $wasAtDepartureAirport = $flight->isAtAirport($departureAirport);
                $wasAtArrivalAirport = $flight->isAtAirport($arrivalAirport);
                $wasAtAlternativeAirport = $flight->isAtAirport($alternativeAirport);

                // update their location
                $flight->current_latitude = ! empty($pilot['latitude']) ? $pilot['latitude'] : null;
                $flight->current_longitude = ! empty($pilot['longitude']) ? $pilot['longitude'] : null;
                $flight->current_altitude = ! empty($pilot['altitude']) ? $pilot['altitude'] : null;
                $flight->current_groundspeed = ! empty($pilot['groundspeed']) ? $pilot['groundspeed'] : null;
                $flight->current_heading = ! empty($pilot['heading']) ? $pilot['heading'] : null;

                // check their new location
                $isAtDepartureAirport = $flight->isAtAirport($departureAirport);
                $isAtArrivalAirport = $flight->isAtAirport($arrivalAirport);
                $isAtAlternativeAirport = $flight->isAtAirport($alternativeAirport);

                // determine if they have departed or arrived at their planned airports
                $departed = $wasAtDepartureAirport && ! $isAtDepartureAirport;
                if ($departed) {
                    $flight->departed_at = $this->lastUpdatedAt;
                }

                $arrivedAtMainAirport = ! $wasAtArrivalAirport && $isAtArrivalAirport;
                $arrivedAtAlternativeAirport = ! $wasAtAlternativeAirport && $isAtAlternativeAirport;
                if ($arrivedAtMainAirport || $arrivedAtAlternativeAirport) {
                    $flight->arrived_at = $this->lastUpdatedAt;
                }
            } else {
                // pilot just connected
                $flight->current_latitude = ! empty($pilot['latitude']) ? $pilot['latitude'] : null;
                $flight->current_longitude = ! empty($pilot['longitude']) ? $pilot['longitude'] : null;
                $flight->current_altitude = ! empty($pilot['altitude']) ? $pilot['altitude'] : null;
                $flight->current_groundspeed = ! empty($pilot['groundspeed']) ? $pilot['groundspeed'] : null;
            }

            $flight->touch();

            $awaitingUpdate->forget([$flight->id]);

            DB::commit();

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        if ($awaitingUpdate->isNotEmpty()) {
            $this->endExpiredPilotSessions($awaitingUpdate);
        }
    }

    /**
     * Update the disconnected_at flag for any pilots not in the latest data feed.
     *
     * @param  Collection  $expiringPilots
     */
    private function endExpiredPilotSessions($expiringPilots)
    {
        $this->info('Ending expired pilot sessions.');

        $progressBar = $this->output->createProgressBar(count($expiringPilots));
        $progressBar->start();

        $expiringPilots->each(function (Pilot $session) use ($progressBar) {
            $session->disconnected_at = $this->lastUpdatedAt;
            $session->save();
            $progressBar->advance();
        });

        $progressBar->finish();
        $this->newLine();
    }

    /**
     * Retrieve and cache an airport from the database.
     *
     * @param    $ident  string
     * @return mixed
     */
    private function getAirport(string $ident)
    {
        return Cache::remember('airport_'.$ident, 720 * 60, function () use ($ident) {
            return Airport::where('icao', $ident)->first();
        });
    }
}
