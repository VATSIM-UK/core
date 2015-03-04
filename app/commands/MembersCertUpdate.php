<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Models\Mship\Account;
use Models\Mship\Account\Email;
use Models\Mship\Account\State;
use Models\Mship\Qualification as QualificationData;
use Models\Mship\Account\Qualification;
use \Cache;
use \VatsimXML;
use \VatsimSSO;
use \Carbon\Carbon;

class MembersCertUpdate extends aCommand {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'Members:CertUpdate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update members using the cert feeds, if they have not had an update in 24 hours.';

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
        global $debug;

        // if specified, turn debug mode on
        if ($this->option("debug")) $debug = TRUE;
        else $debug = FALSE;

        // set the maximum number of members to load, with a hard limit of 10,000
        if ($this->argument('max_members') > 10000) $max_members = 10000;
        else $max_members = $this->argument('max_members');

        // if we only want to force update a specific member, process them and exit
        if ($this->option("force-update")) {
            try {
                $member = Account::findOrFail($this->option("force-update"));
            } catch (Exception $e) {
                echo "\tError: cannot retrieve member " . $this->option("force-update") . " during forced update - " . $e->getMessage();
                exit(1);
            }

            $this->processMember($member);
            exit(0);
        }

        // all accounts should be loaded with their states, emails, and qualifications
        $members = Account::with('states')->with('emails')->with('qualifications');

        // add parameters based on the cron type
        $type = $this->option("type")[0];
        switch($type) {
            case "h":
                // members who have logged in in the last 30 days or who have never been checked
                $members = $members->where('last_login', '>=', Carbon::now()->subMonth()->toDateTimeString())
                                   ->orWhereNull('cert_checked_at');
                if ($debug) echo "Hourly cron.\n";
                break;
            case "d":
                // members who have logged in in the last 90 days and haven't been checked today
                $members = $members->where('cert_checked_at', '<=', Carbon::now()->subHours(23)->toDateTimeString())
                                   ->where('last_login', '>=', Carbon::now()->subMonths(3)->toDateTimeString());
                if ($debug) echo "Daily cron.\n";
                break;
            case "w":
                // members who have logged in in the last 180 days and haven't been checked this week
                $members = $members->where('cert_checked_at', '<=', Carbon::now()->subDays(6)->toDateTimeString())
                                   ->where('last_login', '>=', Carbon::now()->subMonths(6)->toDateTimeString());
                if ($debug) echo "Weekly cron.\n";
                break;
            case "m":
                // members who have never logged in and haven't been checked this month, but are still active VATSIM members
                $members = $members->where('cert_checked_at', '<=', Carbon::now()->subDays(25)->toDateTimeString())
                                   ->whereNull('last_login')
                                   ->where("status", "=", "0");
                if ($debug) echo "Monthly cron.\n";
                break;
            default:
                // all members
                if ($debug) echo "Full cron.\n";
                break;
        }

        $members = $members->orderBy('cert_checked_at', 'ASC')
                           ->limit($max_members)
                           ->get();

        if (count($members) < 1) {
            if ($debug) print "No members to process.\n\n";
            return;
        } elseif ($debug) {
            echo count($members) . " retrieved.\n\n";
        }

        foreach ($members as $pointer => $_m) {
            // remove members we don't want to process
            if ($_m->account_id < 800000) continue;

            $this->processMember($_m, $pointer);
        }

        if ($debug) print "Processed " . ($pointer + 1) . " members.\n\n";
    }


    private function processMember($_m, $pointer=0) {
        global $debug;
        if ($debug) print "#" . ($pointer + 1) . " Processing " . str_pad($_m->account_id, 9, " ", STR_PAD_RIGHT) . "\t";

        // Let's load the details from VatsimXML!
        try {
            $_xmlData = VatsimXML::getData($_m->account_id, "idstatusint");
            if ($debug) print "\tVatsimXML Data retrieved.\n";
        } catch (Exception $e) {
            if ($debug) print "\tVatsimXML Data *NOT* retrieved.  ERROR.\n";
            return;
        }

        if ($_xmlData->name_first == new stdClass() && $_xmlData->name_last == new stdClass()
                && $_xmlData->email == "[hidden]") {
            $_m->delete();
            print "\t" . $_m->account_id . " no longer exists in CERT - deleted.\n";
            return;
        }

        DB::beginTransaction();
        if ($debug) print "\tDB::beginTransaction\n";
        try {
            if (!empty($_xmlData->name_first) && is_string($_xmlData->name_first)) $_m->name_first = $_xmlData->name_first;
            if (!empty($_xmlData->name_last) && is_string($_xmlData->name_last)) $_m->name_last = $_xmlData->name_last;

            if ($debug) print "\t" . str_repeat("-", 89) . "\n";
            if ($debug) print "\t| Data Field\t\tOld Value\t\t\tNew Value\t\t\t|\n";
            if ($_m->isDirty()) {
                $original = $_m->getOriginal();
                foreach ($_m->getDirty() as $key => $newValue) {
                    $this->outputTableRow($key, array_get($original, $key, ""), $newValue);
                }
            }

            $_m->cert_checked_at = Carbon::now()->toDateTimeString();
            $_m->save();
            $_m = $_m->find($_m->account_id);

            // Let's work out the user status.
            $oldStatus = $_m->status;
            $_m->setCertStatus($_xmlData->rating);
            if ($oldStatus != $_m->status) {
                $this->outputTableRow("status", $oldStatus, $_m->status_string);
            }

            // Set their VATSIM registration date.
            $oldDate = $_m->joined_at;
            $newDate = $_xmlData->regdate;
            if ($oldDate != $newDate) {
                $_m->joined_at = $newDate;
                $this->outputTableRow("joined_at", $oldDate, $newDate);
            }

            // If they're in this feed, they're a division member.
            $oldState = ($_m->current_state ? $_m->current_state->state : 0);
            $_m->determineState($_xmlData->region, $_xmlData->division);

            if ($oldState != $_m->current_state->state) {
                $this->outputTableRow("state", $oldState, $_m->current_state);
            }

            // Sort their rating(s) out.
            $atcRating = QualificationData::parseVatsimATCQualification($_xmlData->rating);
            $oldAtcRating = $_m->qualifications()->atc()->orderBy("created_at", "DESC")->first();
            if ($_m->addQualification($atcRating)) {
                $this->outputTableRow("atc_rating", ($oldAtcRating ? $oldAtcRating->code : "None"), $atcRating->code);
            }

            // If their rating is ABOVE INS1 (8+) then let's get their last.
            if ($_xmlData->rating >= 8) {
                $_prevRat = VatsimXML::getData($_m->account_id, "idstatusprat");
                if (isset($_prevRat->PreviousRatingInt)) {
                    $prevAtcRating = QualificationData::parseVatsimATCQualification($_prevRat->PreviousRatingInt);
                    if ($_m->addQualification($prevAtcRating)) {
                        $this->outputTableRow("atc_rating", "Previous", $prevAtcRating->code);
                    }
                }
            } else {
                // remove any extra ratings
                foreach (($q = $_m->qualifications_atc_training) as $qual) $qual->delete(); 
                foreach (($q = $_m->qualifications_pilot_training) as $qual) $qual->delete(); 
                foreach (($q = $_m->qualifications_admin) as $qual) $qual->delete();
            }

            $pilotRatings = QualificationData::parseVatsimPilotQualifications($_xmlData->pilotrating);
            foreach ($pilotRatings as $pr) {
                if ($_m->addQualification($pr)) {
                    $this->outputTableRow("pilot_rating", "n/a", $pr->code);
                }
            }

            $_m->save();
        } catch (Exception $e) {
            DB::rollback();
            print "\tDB::rollback\n";
            print "\tError: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile() . "\n";
            print "\tCID: " . $_m->account_id . "\n";
        }

        if ($debug) print "\t" . str_repeat("-", 89) . "\n";

        DB::commit();
        if ($debug) print "\tDB::commit\n";
        if ($debug) print "\n";
    }

    private function outputTableRow($key, $old, $new) {
        global $debug;
        if ($debug) print "\t| " . str_pad($key, 20, " ", STR_PAD_RIGHT) . "\t" . str_pad($old, 30, " ", STR_PAD_RIGHT) . "\t" . str_pad($new, 30, " ", STR_PAD_RIGHT) . "\t|\n";
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments() {
        return array(
            array("max_members", InputArgument::OPTIONAL, "The number of members to process in a single run.", 1000),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return array(
            array("force-update", "f", InputOption::VALUE_OPTIONAL, "If specified, only this CID will be checked.", 0),
            array("type", "t", InputOption::VALUE_OPTIONAL, "Which update are we running? Hourly, Daily, Weekly or Monthly?", "hourly"),
            array("debug", "d", InputOption::VALUE_NONE, "Enable debug output."),
        );
    }
}