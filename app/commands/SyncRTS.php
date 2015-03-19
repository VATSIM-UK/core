<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Models\Mship\Account;

class SyncRTS extends aCommand {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'Sync:RTS';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync membership data from Core to the RTS system.';

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

        if ($debug) print "RTS DIVISION DATABASE IMPORT STARTED\n\n";

        $members = DB::connection('mysql.rts')->table('members');
        if ($this->option("force-update")) {
            $members->where('cid', '=', $this->option('force-update'))
                    ->where('deleted', '=', '0');
        } else {
            $members->where('deleted', '=', '0')
                    ->orderBy('cid');
        }

        $members = $members->get();

        if ($debug) print "Querying members...";
        $numupdated = 0;

        if ($debug) print "OK.\n\n";

        foreach ($members as $mem) {
            if ($debug) print "Updating {$mem->cid} ";
            if (self::pullCoreData($mem->cid, $mem->visiting)) {
              if ($debug) print "...... Successful\n";
            }
            elseif ($debug) print "...... FAILED\n";
            $numupdated++;
        }
        if ($debug) print "\n\n";
        if ($debug) print "$numupdated members were updated";
        if ($debug) print "\nRTS SYNC COMPLETED\n\n";

    }

    protected function pullCoreData($cid, $ignoreRating=false) {
        // get account
        try {
            $member = Account::findOrFail($cid);
        } catch (Exception $e) {
            if ($debug) echo "\tError: cannot retrieve member " . $cid . " from Core - " . $e->getMessage();
            return FALSE;
        }

        // calculate pilot rating
        $pRating = 0;
        $pQuals = $member->qualifications_pilot;
        if (count($pQuals) > 0) {
            foreach ($pQuals as $qual) {
                $pRating += $qual->qualification->vatsim;
            }
        }

        // set and process data
        $updateData = array(
            'name' => $member->name_first . ' ' . $member->name_last,
            'email' => $member->primary_email,
            'rating' => $member->qualification_atc->qualification->vatsim,
            'prating' => $pRating,
            'last_cert_check' => $member->cert_checked_at
        );

        if ($member->network_banned || $member->inactive) $updateData['rating'] = 0;
        if ($ignoreRating) {
            unset($updateData['rating']);
            unset($updateData['prating']);
        }
        if (empty($updateData['email'])) unset($updateData['email']);

        $members = DB::connection('mysql.rts')
                        ->table('members')
                        ->where('cid', '=', $cid)
                        ->update($updateData);

        return TRUE;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return array(
            array("force-update", "f", InputOption::VALUE_OPTIONAL, "If specified, only this CID will be checked.", 0),
            array("debug", "d", InputOption::VALUE_NONE, "Enable debug output."),
        );
    }
}
