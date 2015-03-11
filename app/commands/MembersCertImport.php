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

class MembersCertImport extends aCommand {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'Members:CertImport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all members from CERT AutoTools';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    private function out($line){
        if($this->option("debug") == true){
            $this->info($line);
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire() {
        // Let's cache the membership file, unless there's an overriding argument!
        if (Cache::has("cert.autotools.divdb.file")) {
            $members = Cache::get("cert.autotools.divdb.file");
        } else {
            $members = file("https://cert.vatsim.net/vatsimnet/admin/divdbfullwpilot.php?authid=" . $_ENV['vatsim.cert.at.user'] . "&authpassword=" . urlencode($_ENV['vatsim.cert.at.pass'])."&div=".$_ENV['vatsim.cert.at.div']);
            Cache::put("cert.autotools.divdb.file", $members, \Carbon\Carbon::now()->addHours(11)->addMinutes(30));
            Cache::put("cert.autotools.divdb.pointer", 0, \Carbon\Carbon::now()->addHours(11)->addMinutes(30));
        }

        // Get all current members of the database.
        $existingMembers = Account::select("account_id")->orderBy("account_id", "ASC")->get();

        if (count($members) < 1) {
            $this->error("No members to process.\n\n");
            return;
        }

        $pointer = 0;
        foreach ($members as $m) {
            $m = str_getcsv($m, ",", "");

            $this->out("#" . $pointer . " Processing " . str_pad($m[0], 9, " ", STR_PAD_RIGHT) . "\t");

            // Do they currently exist? We only process new members!
            if(binary_search($m[0], $existingMembers, "account_id", 0, $existingMembers->count()-1)){
                continue;
            }

            // Find or create?{
            $_m = new Account(array("account_id" => $m[0]));

            DB::beginTransaction();
            $this->out("\tDB::beginTransaction\n");
            try {
                $_m->name_first = $m[3];
                $_m->name_last = $m[4];

                $this->out("\t" . str_repeat("-", 89) . "\n");
                $this->out("\t| Data Field\t\tOld Value\t\t\tNew Value\t\t\t|\n");
                if ($_m->isDirty()) {
                    $original = $_m->getOriginal();
                    foreach ($_m->getDirty() as $key => $newValue) {
                        $this->outputTableRow($key, array_get($original, $key, ""), $newValue);
                    }
                }

                $_m->cert_checked_at = \Carbon\Carbon::now("UTC")->toDateTimeString();
                $_m->save();
                $_m = $_m->find($m[0]);

                // Let's work out the user status.
                $oldStatus = $_m->status;
                $_m->setCertStatus($m[1]);

                if($oldStatus != $_m->status){
                    $this->outputTableRow("status", $oldStatus, $_m->status);
                }

                // If they're in this feed, they're a division member.
                $oldState = ($_m->current_state ? $_m->current_state->state : 0);
                $_m->determineState($m[12], $m[13]);

                if ($_m->current_state && $oldState != $_m->current_state->state) {
                    $this->outputTableRow("state", $oldState, $_m->current_state);
                }

                // Sort their rating(s) out.
                $atcRating = QualificationData::parseVatsimATCQualification($m[1]);
                $oldAtcRating = $_m->qualifications()->atc()->orderBy("created_at", "DESC")->first();
                if ($_m->addQualification($atcRating)) {
                    $this->outputTableRow("atc_rating", ($oldAtcRating ? $oldAtcRating->code : "None"), $atcRating->code);
                }

                // If their rating is ABOVE INS1 (8+) then let's get their last.
                if ($m[1] >= 8) {
                    $_prevRat = VatsimXML::getData($m[0], "idstatusprat");
                    if (isset($_prevRat->PreviousRatingInt)) {
                        $prevAtcRating = QualificationData::parseVatsimATCQualification($_prevRat->PreviousRatingInt);
                        if ($_m->addQualification($prevAtcRating)) {
                            $this->outputTableRow("atc_rating", "Previous", $prevAtcRating->code);
                        }
                    }
                }

                $pilotRatings = QualificationData::parseVatsimPilotQualifications($m[2]);
                foreach ($pilotRatings as $pr) {
                    if ($_m->addQualification($pr)) {
                        $this->outputTableRow("pilot_rating", "n/a", $pr->code);
                    }
                }

                // Add their email.
                $oldPrimaryEmail = ($_m->primary_email ? $_m->primary_email->email : "");
                if($_m->addEmail($m[5], true, true)){
                    $this->outputTableRow("primary_email", $oldPrimaryEmail, $m[5]);
                }

                $_m->save();
            } catch (Exception $e) {
                DB::rollback();
                $this->out("\tDB::rollback\n");
                $this->error("\tError: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile() . "\n");
            } finally {
                $this->out("\t" . str_repeat("-", 89) . "\n");
                $pointer++;
                Cache::put("cert.autotools.divdb.pointer", $pointer, \Carbon\Carbon::now()->addHours(11)->addMinutes(30));

                DB::commit();
                $this->out("\tDB::commit\n");
            }

            $this->out("\n");
        }

        //$this->out("Processed " . ($pointerStart - $pointer) . " members.\n\n");
    }

    private function outputTableRow($key, $old, $new) {
        $this->out("\t| " . str_pad($key, 20, " ", STR_PAD_RIGHT) . "\t" . str_pad($old, 30, " ", STR_PAD_RIGHT) . "\t" . str_pad($new, 30, " ", STR_PAD_RIGHT) . "\t|\n");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments() {
        return array(
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return array(
            array("debug", "debug", InputOption::VALUE_OPTIONAL, "Turn on the debugging output.", false),
        );
    }

}
