<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Email;
use App\Models\Mship\Account\State;
use App\Models\Mship\Qualification as QualificationData;
use App\Models\Mship\Account\Qualification;
use DB;
use VatsimXML;

class MembersRtsImport extends aCommand {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'Members:RtsImport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import old membership data from the RTS System.';

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
        // Let's load the necessary members from the database.
        $members = Account::all();

        if (count($members) < 1) {
            print "No members to process.\n\n";
            return;
        }

        foreach ($members as $key => $m) {
            print "#" . $key . " Processing " . str_pad($m[0], 9, " ", STR_PAD_RIGHT) . "\t";

            DB::beginTransaction();
            print "\tDB::beginTransaction\n";
            try {
                // Let's get

                print "\t" . str_repeat("-", 89) . "\n";
                print "\t| Data Field\t\tOld Value\t\t\tNew Value\t\t\t|\n";
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

                if ($oldState != $_m->current_state->state) {
                    $this->outputTableRow("state", $oldState, $_m->current_state);
                }

                // Sort their rating(s) out.
                $atcRating = QualificationData::parseVatsimATCQualification($m[1]);
                $oldAtcRating = $_m->qualificationsAtc()->orderBy("created_at", "DESC")->first();
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
                print "\tDB::rollback\n";
                print "\tError: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile() . "\n";
            } finally {
                print "\t" . str_repeat("-", 89) . "\n";
                $pointer++;
                Cache::put("cert.autotools.divdb.pointer", $pointer, \Carbon\Carbon::now()->addHours(11)->addMinutes(30));

                DB::commit();
                print "\tDB::commit\n";
            }

            print "\n";
        }

        print "Processed " . ($pointerStart - $pointer) . " members.  Pointer from " . $pointerStart . " to " . $pointer . "\n\n";
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
        );
    }

}
