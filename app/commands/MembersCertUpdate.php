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
        if (!$this->option("logged-in-since") && !$this->option("not-logged-in-since")) exit("Please specify either --logged-in-since or --not-logged-in-since.");
        if ($this->option("debug")) $debug = TRUE;
        else $debug = FALSE;

        if ($this->option("force-update")) {
            try {
                $member = Account::findOrFail($this->option("force-update"));
            } catch (Exception $e) {
                echo "\tError: cannot retrieve member " . $this->option("force-update") . " during forced update - " . $e->getMessage();
                exit(1);
            }

            $this->processMember($member);
            exit(0);

        } else {

            /*
             * REGULAR CHECKING:    php artisan Members:CertUpdate --logged-in-since --last-login=720
             * OCCASIONAL CHECKING: php artisan Members:CertUpdate --not-logged-in-since --last-login=720
             */
            $members = Account::with('states')->with('emails')->with('qualifications')->where(function($query) {
                $query->where("cert_checked_at", "<=", \Carbon\Carbon::now()->subHours($this->option("time-since-last"))->toDateTimeString())
                      ->orWhereNull("cert_checked_at");
            });
            // never process a member who hasn't logged in for greater than 6 months
            if (!$this->option("remove-hard-limit")) $members = $members->where("last_login", "<=", \Carbon\Carbon::now()->subHours(24*30*6));
            // for regular/active member checking
            // if set, AND process members who has logged in since x
            if ($this->option("logged-in-since")) $members = $members->where("last_login", "<=", \Carbon\Carbon::now()->subHours($this->option("logged-in-since"))->toDateTimeString());
            // for irregular/less-active member checking
            // if set, AND process members who haven't logged in since x, or haven't ever logged in and aren't suspended
            elseif ($this->option("not-logged-in-since")) $members = $members->where(function($query) {
                $query->where("last_login", ">=", \Carbon\Carbon::now()->subHours($this->option("last-login"))->toDateTimeString())
                      ->orWhere(function($query) {
                            $query->whereNull("last_login")
                                  ->where("status", "=", "0");
                      });
            });
            // AND only process members who haven't been updated recently, or ever
            $members = $members->orderBy("cert_checked_at", "ASC")
                               ->limit($this->argument("max_members"))
                               ->get();
        }

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

            $_m->cert_checked_at = \Carbon\Carbon::now()->toDateTimeString();
            $_m->save();
            $_m = $_m->find($_m->account_id);

            // Let's work out the user status.
            $oldStatus = $_m->status_string;
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
            array("last-login", "l", InputOption::VALUE_OPTIONAL, "The amount of time (in hours) that a user has to have logged in within to force an update.", 24*90),
            array("logged-in-since", "s", InputOption::VALUE_NONE, "Process members that have logged in since the specified login time."),
            array("not-logged-in-since", "o", InputOption::VALUE_NONE, "Process members that have not logged in since the specified login time."),
            array("time-since-last", "t", InputOption::VALUE_OPTIONAL, "The amount of time (in hours) that has to have lapsed to force an update.", 2),
            array("remove-hard-limit", "r", InputOption::VALUE_OPTIONAL, "Removes the hard time limit of 6 months.", 2),
            array("force-update", "f", InputOption::VALUE_OPTIONAL, "If specified, only this CID will be checked.", 0),
            array("debug", "d", InputOption::VALUE_NONE, "Enable debug output."),
        );
    }
}