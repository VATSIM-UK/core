<?php

namespace App\Console\Commands;

use App\Libraries\AutoTools;
use App\Models\Mship\Account;
use App\Models\Mship\Account\State;
use App\Models\Mship\Qualification;
use DB;
use VatsimXML;

/**
 * Utilizes the CERT divdb file to import new users and update existing user emails.
 */
class MembersCertImport extends aCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'Members:CertImport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import/update member emails from CERT AutoTools';

    protected $count_new = 0;
    protected $count_emails = 0;
    protected $count_none = 0;
    protected $member_list;
    protected $member_email_list;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // get a list of current members and their emails
        // accounts for the possibility of a member not having a (primary) email in mship_account_email
        $this->member_list = $this->getMemberIds();
        $this->member_email_list = $this->getMemberEmails();

        $this->log('Member list and email list obtained.');

        $members = AutoTools::getDivisionData();
        foreach ($members as $index => $member) {
            $this->log("Processing {$member[0]} {$member[3]} {$member[4]}: ", null, false);
            DB::transaction(function () use ($member) {
                $this->processMember($member);
            });
        }

        $this->sendSlackSuccess('Members imported.', [
            'New Members:' => $this->count_new,
            'Member Emails Updated:' => $this->count_emails,
            'Unchanged Members:' => $this->count_none
        ]);
    }

    protected function processMember($member)
    {
        // if member doesn't exist, create them, otherwise check/update their email
        if (!array_key_exists($member[0], $this->member_list)) {
            $this->createNewMember($member);
            $this->log('created new account');
            $this->count_new++;
        } else {
            $current_email = array_key_exists($member[0], $this->member_email_list)
                ? $this->member_email_list[$member[0]]
                : false;
            if (strcasecmp($current_email, $member[5]) !== 0) {
                $this->updateMemberEmail($member);
                $this->log('updated member email');
                $this->count_emails++;
            } else {
                $this->log('no changes needed');
                $this->count_none++;
            }
        }
    }

    protected function createNewMember($member_data)
    {
        $member = new Account();
        $member->account_id = $member_data[0];
        $member->name_first = $member_data[3];
        $member->name_last = $member_data[4];
        $member->joined_at = $member_data[11];
        $member->is_inactive = (boolean) ($member_data[1] < 0);
        $member->save();
        $member->addEmail($member_data[5], true, true);
        $member->determineState($member_data[12], $member_data[13]);

        // if they have an extra rating, log their previous rating first,
        // regardless of whether it will be overwritten
        if ($member_data[1] >= 8) {
            $_prevRat = VatsimXML::getData($member->account_id, 'idstatusprat');
            if (isset($_prevRat->PreviousRatingInt)) {
                $prevAtcRating = Qualification::parseVatsimATCQualification($_prevRat->PreviousRatingInt);
                $member->addQualification($prevAtcRating);
            } else {
                $this->sendSlackError('Member\'s previous rating is unavailable.', $member->account_id);
            }
        }

        // if they're a division member, or their current rating isn't instructor, log their 'main' rating
        if (($member_data[1] != 8 && $member_data[1] != 9)
            || $member->current_state->state === State::STATE_DIVISION
        ) {
            $member->addQualification(Qualification::parseVatsimATCQualification($member_data[1]));
        }

        // anything else is processed by the Members:CertUpdate script
    }

    protected function updateMemberEmail($member_data)
    {
        $member = Account::find($member_data[0]);
        $member->addEmail($member_data[5], true, true);
    }

    protected function getMemberIds()
    {
        return DB::table('mship_account')
            ->pluck('account_id', 'account_id');
    }

    protected function getMemberEmails()
    {
        return DB::table('mship_account_email')
            ->where('is_primary', 1)
            ->pluck('email', 'account_id');
    }
}
