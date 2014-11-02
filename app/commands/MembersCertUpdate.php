<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Models\Mship\Account\Account;
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
        $members = Account::where("cert_checked_at", "<=", \Carbon\Carbon::now()->subHours($this->option("time_since_last"))->toDateTimeString())
                ->orderBy("cert_checked_at", "ASC")
                ->limit($this->argument("max_members"))
                ->get();

        if (count($members) < 1) {
            print "No members to process.\n\n";
            return;
        }

        foreach ($members as $pointer => $_m) {
            print "#" . ($pointer + 1) . " Processing " . str_pad($_m->account_id, 9, " ", STR_PAD_RIGHT) . "\t";

            // Let's load the details from VatsimXML!
            try {
                $_xmlData = VatsimXML::getData($_m->account_id);
                print "\tVatsimXML Data retrieved.\n";
            } catch (Exception $e) {
                print "\tVatsimXML Data *NOT* retrieved.  ERROR.\n";
                continue;
            }

            DB::beginTransaction();
            print "\tDB::beginTransaction\n";
            try {
                $_m->name_first = $_xmlData->name_first;
                $_m->name_last = $_xmlData->name_last;

                print "\t" . str_repeat("-", 89) . "\n";
                print "\t| Data Field\t\tOld Value\t\t\tNew Value\t\t\t|\n";
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
                $oldStatus = $_m->status;
                $_m->setCertStatus($_xmlData->rating);

                if ($oldStatus != $_m->status) {
                    $this->outputTableRow("status", $oldStatus, $_m->status);
                }

                // If they're in this feed, they're a division member.
                $oldState = ($_m->current_state ? $_m->current_state->state : 0);
                $_m->determineState($_xmlData->region, $_xmlData->division);

                if ($oldState != $_m->current_state->state) {
                    $this->outputTableRow("state", $oldState, $_m->current_state);
                }

                // Sort their rating(s) out.
                $atcRating = QualificationData::parseVatsimATCQualification($_xmlData->rating);
                $oldAtcRating = $_m->qualificationsAtc()->orderBy("created_at", "DESC")->first();
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
            } finally {
                print "\t" . str_repeat("-", 89) . "\n";

                DB::commit();
                print "\tDB::commit\n";
            }

            print "\n";
        }

        print "Processed " . ($pointer + 1) . " members.\n\n";
    }

    private function outputTableRow($key, $old, $new) {
        print "\t| " . str_pad($key, 20, " ", STR_PAD_RIGHT) . "\t" . str_pad($old, 30, " ", STR_PAD_RIGHT) . "\t" . str_pad($new, 30, " ", STR_PAD_RIGHT) . "\t|\n";
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments() {
        return array(
            array("max_members", InputArgument::OPTIONAL, "The number of members to process in a single run.", 100),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return array(
            array("time_since_last", "elapsed", InputOption::VALUE_OPTIONAL, "The amount of time (in hours) that has to have lapsed to force an update.", 24),
        );
    }

}
