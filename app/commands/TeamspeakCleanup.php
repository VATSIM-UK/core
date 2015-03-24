<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Carbon\Carbon;

use Controllers\Teamspeak\TeamspeakAdapter;
use Models\Mship\Account;
use Models\Mship\Qualification;
use Models\Teamspeak\Registration;
use Models\Teamspeak\Ban;
use Models\Teamspeak\Log;

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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire() {
        if ($this->option("debug")) $debug = TRUE;
        else $debug = FALSE;

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
                        if ($debug) echo "No registration found: {$client['cldbid']} {$client['client_nickname']} {$client['client_unique_identifier']}\n";
                        $offset--;
                    } catch (Exception $e) {
                        if ($debug) echo $e->getMessage();
                        if ($debug) echo "Deletion failed: {$client['cldbid']} {$client['client_nickname']} {$client['client_unique_identifier']}\n";
                    }
                } elseif ($debug) {
                    echo "Registration found: {$client['cldbid']} {$client['client_nickname']} {$client['client_unique_identifier']}\n";
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
            if ($debug) echo "Old registration deleted: {$registration->id}\n";
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return array(
            array("debug", "d", InputOption::VALUE_NONE, "Enable debug output."),
        );
    }
}
