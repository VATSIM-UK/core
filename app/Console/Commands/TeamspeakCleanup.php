<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Carbon\Carbon;

use App\Http\Controllers\Teamspeak\TeamspeakAdapter;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Teamspeak\Registration;
use App\Models\Teamspeak\Ban;
use App\Models\Teamspeak\Log;

class TeamspeakCleanup extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'TeaMan:CleanUp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up the Core and TS database.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $tscon = TeamSpeakAdapter::run("VATSIM UK Cleanup Bot");

        // check TS database for clients without registrations
        $total_clients = $tscon->clientCountDb();
        $offset = 0;
        while ($offset < $total_clients) {
            $clients = $tscon->clientListDb($offset);
            foreach ($clients as $client) {
                $registered = Registration::
                                  where('uid', '=', $client['client_unique_identifier'])
                                ->where('dbid', '=', $client['cldbid'])
                                ->exists();
                if (!$registered) {
                    try {
                        $tscon->clientDeleteDb($client['cldbid']);
                        $this->log("No registration found: {$client['cldbid']} {$client['client_nickname']} {$client['client_unique_identifier']}");
                        $offset--;
                    } catch (Exception $e) {
                        $this->log($e->getMessage());
                        $this->log("Deletion failed: {$client['cldbid']} {$client['client_nickname']} {$client['client_unique_identifier']}");
                    }
                } else {
                    $this->log("Registration found: {$client['cldbid']} {$client['client_nickname']} {$client['client_unique_identifier']}");
                }
            }
            $offset += 25;
        }

        // check Core database for incomplete registrations and registrations older than 6 months

        $old_registrations =
            Registration::where('last_login', '<', Carbon::now()->subMonths(6)->toDateTimeString())
                ->orWhere(function($query) {
                    $query->where('status', '=', 'new')
                          ->where('created_at', '<', Carbon::now()->subWeek()->toDateTimeString());
                })->get();
        foreach ($old_registrations as $registration) {
            $registration->delete($tscon);
            $this->log("Old registration deleted: {$registration->id}");
        }
    }
}
