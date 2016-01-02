<?php

namespace App\Console\Commands;

use App\Models\Mship\Account;
use App\Models\Mship\Qualification as QualificationData;
use Carbon\Carbon;
use VatsimXML;
use Exception;
use DB;

class MembersCertUpdate extends aCommand {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'Members:CertUpdate
                        {max_members=1000}
                        {--t|type=all : Which update are we running? Hourly, Daily, Weekly or Monthly?}
                        {--f|force= : If specified, only this CID will be checked.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update members using the CERT feeds.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        // if we only want to force update a specific member, process them and exit
        if ($this->option('force-update')) {
            $member = Account::findOrFail($this->option('force-update'));
            $this->processMember($member);
            exit(0);
        }

        $members = $this->getMembers();

        if (count($members) < 1) {
            $this->log("No members to process.");
            return;
        }

        $this->log(count($members) . " retrieved.");

        foreach ($members as $pointer => $_m) {
            // remove members we don't want to process
            if ($_m->account_id < 800000) continue;

            $this->processMember($_m, $pointer);
        }
    }

    protected function getMembers()
    {
        // all accounts should be loaded with their states, emails, and qualifications
        $members = Account::with('states', 'emails', 'qualifications');

        // add parameters based on the cron type
        $type = $this->option("type")[0];
        switch($type) {
            case 'h':
                // members who have logged in in the last 30 days or who have never been checked
                $members->where('last_login', '>=', Carbon::now()->subMonth())
                    ->orWhereNull('cert_checked_at');
                break;
            case 'd':
                // members who have logged in in the last 90 days and haven't been checked today
                $members->where('cert_checked_at', '<=', Carbon::now()->subHours(23))
                    ->where('last_login', '>=', Carbon::now()->subMonths(3)->toDateTimeString());
                break;
            case 'w':
                // members who have logged in in the last 180 days and haven't been checked this week
                $members->where('cert_checked_at', '<=', Carbon::now()->subDays(6))
                    ->where('last_login', '>=', Carbon::now()->subMonths(6)->toDateTimeString());
                break;
            case 'm':
                // members who have never logged in and haven't been checked this month, but are still active VATSIM members
                $members->where('cert_checked_at', '<=', Carbon::now()->subDays(25))
                    ->whereNull('last_login')
                    ->where('status', 0);
                break;
        }

        return $members->orderBy('cert_checked_at', 'ASC')
            ->limit($this->argument('max_members'))
            ->get();
    }

    private function processMember($_m) {
        // Let's load the details from VatsimXML!
        try {
            $_xmlData = VatsimXML::getData($_m->account_id, "idstatusint");
        } catch (Exception $e) {
            return;
        }

        if ($_xmlData->name_first == new \stdClass() && $_xmlData->name_last == new \stdClass()
                && $_xmlData->email == "[hidden]") {
            $_m->delete();
            return;
        }

        DB::beginTransaction();
        try {
            if (!empty($_xmlData->name_first) && is_string($_xmlData->name_first)) $_m->name_first = $_xmlData->name_first;
            if (!empty($_xmlData->name_last) && is_string($_xmlData->name_last)) $_m->name_last = $_xmlData->name_last;

            $_m->cert_checked_at = Carbon::now()->toDateTimeString();
            $_m->save();
            $_m = $_m->find($_m->account_id);

            // Let's work out the user status.
            $_m->is_inactive = (boolean) ($_xmlData->rating < 0);

            // Are they network banned, but unbanned in our system?
            // Add it!
            if($_xmlData->rating == 0 && $_m->is_network_banned === false){
                // Add a ban.
                $newBan = new \App\Models\Mship\Account\Ban();
                $newBan->type = \App\Models\Mship\Account\Ban::TYPE_NETWORK;
                $newBan->reason_extra = "Network ban discovered via Cert update scripts.";
                $newBan->period_start = \Carbon\Carbon::now();
                $newBan->save();

                $_m->bans()->save($newBan);
                Account::find(VATSIM_ACCOUNT_SYSTEM)->bansAsInstigator($newBan);
            }

            // Are they banned in our system (for a network ban) but unbanned on the network?
            // Then expire the ban.
            if($_m->is_network_banned === true && $_xmlData->rating > 0){
                $ban = $_m->network_ban;
                $ban->period_finish = \Carbon\Carbon::now();
                $ban->setPeriodAmountFromTS();
                $ban->save();
            }

            // Set their VATSIM registration date.
            $_m->joined_at = $newDate;

            // If they're in this feed, they're a division member.
            $_m->determineState($_xmlData->region, $_xmlData->division);

            // Sort their rating(s) out - we're not permitting instructor ratings if they're NONE UK members.
            if(($_xmlData->rating != 8 AND $_xmlData->rating != 9) OR $_m->current_state->state == \App\Models\Mship\Account\State::STATE_DIVISION){
                $atcRating = QualificationData::parseVatsimATCQualification($_xmlData->rating);
                $_m->addQualification($atcRating);
            }

            // If their rating is ABOVE INS1 (8+) then let's get their last.
            if ($_xmlData->rating >= 8) {
                $_prevRat = VatsimXML::getData($_m->account_id, "idstatusprat");
                if (isset($_prevRat->PreviousRatingInt)) {
                    $prevAtcRating = QualificationData::parseVatsimATCQualification($_prevRat->PreviousRatingInt);
                    $_m->addQualification($prevAtcRating);
                }
            } else {
                // remove any extra ratings
                foreach (($q = $_m->qualifications_atc_training) as $qual) {
                    $qual->delete();
                }
                foreach (($q = $_m->qualifications_pilot_training) as $qual) {
                    $qual->delete();
                }
                foreach (($q = $_m->qualifications_admin) as $qual) {
                    $qual->delete();
                }
            }

            $pilotRatings = QualificationData::parseVatsimPilotQualifications($_xmlData->pilotrating);
            foreach ($pilotRatings as $pr) {
                $_m->addQualification($pr);
            }

            $_m->save();

        } catch (Exception $e) {
            DB::rollback();
            print "\tDB::rollback\n";
            print "\tError: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile() . "\n";
            print "\tCID: " . $_m->account_id . "\n";
        }

        DB::commit();
    }
}
