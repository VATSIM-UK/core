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
        //set_time_limit(0);
        require_once '/var/www/rts/config/config.php';
        print "RTS DIVISION DATABASE IMPORT STARTED\n\n";

        if ($this->option("force-update")) {
            $members_q = mysql_query("SELECT * FROM `members`
                                      WHERE `cid` = {$this->option('force-update')}
                                      AND `deleted` = 0", $rtsdb);
        } else {
            $members_q = mysql_query("SELECT * FROM `members`
                                      WHERE `deleted` = 0
                                      ORDER BY `cid` ASC", $rtsdb);
        }

        print "Querying members...";
        $numupdated = 0;

        print "OK.\n\n";

        while ($mem = mysql_fetch_assoc($members_q)) {
            print "Updating {$mem['cid']} ";
            ob_flush();
            if (self::pullCoreData($mem['cid'], $mem['visiting'])) print "...... Successful\n";
            else print "...... FAILED\n";
            $numupdated++;
        }
        print "\n\n";
        print "$numupdated members were updated";
        print "\nRTS SYNC COMPLETED\n\n";

    }

    protected function pullCoreData($cid, $ignoreRating=false) {
        // get account
        try {
            $member = Account::findOrFail($cid);
        } catch (Exception $e) {
            echo "\tError: cannot retrieve member " . $cid . " from Core - " . $e->getMessage();
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

        updateUser($member->id, $updateData);

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
