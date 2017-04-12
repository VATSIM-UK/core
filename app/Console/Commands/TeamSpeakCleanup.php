<?php

namespace App\Console\Commands;

use Exception;
use Carbon\Carbon;
use App\Libraries\TeamSpeak;
use App\Models\TeamSpeak\Registration;

class TeamSpeakCleanup extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'TeaMan:CleanUp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up the Core and TeamSpeak database.';

    protected $tscon;

    protected function initialise()
    {
        $this->tscon = TeamSpeak::run('VATSIM UK Cleanup Bot');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->initialise();

        // check TS database for clients without registrations
        $total_clients = $this->tscon->clientCountDb();
        $offset = 0;
        while ($offset < $total_clients) {
            $clients = $this->tscon->clientListDb($offset);
            foreach ($clients as $client) {
                $offset -= $this->checkRegistration($client);
            }

            $offset += 25;
        }

        // check Core database for incomplete registrations and registrations older than 6 months
        $old_registrations = Registration::where('last_login', '<', Carbon::now()->subMonths(6))
            ->orWhere(function ($query) {
                $query->whereNull('dbid')
                    ->where('created_at', '<', Carbon::now()->subWeek()->toDateTimeString());
            })->get();

        foreach ($old_registrations as $registration) {
            $registration->delete($this->tscon);
            $this->log("Old registration deleted: {$registration->id}");
        }

        $this->sendSlackSuccess();
    }

    /**
     * Check the registration for a TeamSpeak client.
     *
     * @param $client The client being checked.
     * @return int Returns 0 if no change has been made, or 1 if the client was deleted.
     */
    protected function checkRegistration($client)
    {
        $isRegistered = Registration::where('uid', $client['client_unique_identifier'])
            ->where('dbid', $client['cldbid'])
            ->exists();

        if (!$isRegistered) {
            try {
                $this->tscon->clientDeleteDb($client['cldbid']);
                $this->log("No registration found: {$client['cldbid']} {$client['client_nickname']} {$client['client_unique_identifier']}");

                return 1;
            } catch (Exception $e) {
                $this->log($e->getMessage());
                $message = "Deletion failed: {$client['cldbid']} {$client['client_nickname']} {$client['client_unique_identifier']}";
                $this->log($message);
                $this->sendSlackError($message);

                return 0;
            }
        } else {
            $this->log("Registration found: {$client['cldbid']} {$client['client_nickname']} {$client['client_unique_identifier']}");

            return 0;
        }
    }
}
