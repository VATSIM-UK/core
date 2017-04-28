<?php

namespace App\Console\Commands;

use DB;
use VatsimXML;
use App\Models\Mship\State;
use App\Libraries\AutoTools;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Exceptions\Mship\DuplicateStateException;
use App\Exceptions\Mship\DuplicateQualificationException;

/**
 * Utilizes the CERT divdb file to import new users and update existing user emails.
 */
class MembersCertImport extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'Members:CertImport {--full}';

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
        $this->member_list = $this->getMemberIdAndEmail();

        $this->log('Member list and email list obtained.');

        $members = AutoTools::getDivisionData(!$this->option('full'));

        foreach ($members as $member) {
            $this->log("Processing {$member['cid']} {$member['name_first']} {$member['name_last']}: ", null, false);

            DB::transaction(function () use ($member) {
                $this->processMember($member);
            });
        }

        $this->sendSlackSuccess('Members imported.', [
            'New Members:' => $this->count_new,
            'Member Emails Updated:' => $this->count_emails,
            'Unchanged Members:' => $this->count_none,
        ]);
    }

    protected function processMember($member)
    {
        if (array_get($this->member_list, $member['cid'], 'unknown') != 'unknown') {
            if (strcasecmp($this->member_list[$member['cid']], $member['email']) == 0) {
                $this->updateMember($member);
                $this->log('updated member');
                $this->count_emails++;

                return;
            }

            $this->log('no important changes required.');
            $this->count_none++;

            return;
        }

        $this->createNewMember($member);
        $this->log('created new account');
        $this->count_new++;
    }

    protected function createNewMember($member_data)
    {
        $member = new Account([
            'id' => $member_data['cid'],
            'name_first' => $member_data['name_first'],
            'name_last' => $member_data['name_last'],
            'email' => $member_data['email'],
            'joined_at' => $member_data['reg_date'],
        ]);
        $member->is_inactive = (bool) ($member_data['rating_atc'] < 0);
        $member->save();

        try {
            $member->addState(State::findByCode('DIVISION'), 'EUR', 'GBR');
        } catch (DuplicateStateException $e) {
            // TODO: Do something.
        }

        // if they have an extra rating, log their previous rating first,
        // regardless of whether it will be overwritten
        if ($member_data['rating_atc'] >= 8) {
            $_prevRat = VatsimXML::getData($member->id, 'idstatusprat');

            if (isset($_prevRat->PreviousRatingInt)) {
                $prevAtcRating = Qualification::parseVatsimATCQualification($_prevRat->PreviousRatingInt);

                try {
                    if ($prevAtcRating) {
                        $member->addQualification($prevAtcRating);
                    }
                } catch (DuplicateQualificationException $e) {
                    // TODO: Something.
                } catch (Exception $e) {
                    // TODO: Something.
                }
            } else {
                $this->sendSlackError('Member\'s previous rating is unavailable.', $member->id);
            }
        }

        // if they're a division member, or their current rating isn't instructor, log their 'main' rating
        if (($member_data['rating_atc'] < 8) || $member->hasState('DIVISION')) {
            try {
                $atcRating = Qualification::parseVatsimATCQualification($member_data['rating_atc']);

                if ($atcRating) {
                    $member->addQualification($atcRating);
                }
            } catch (DuplicateQualificationException $e) {
                // TODO: Something.
            } catch (ErrorException $e) {
                // TODO: Something.
            }
        }

        // anything else is processed by the Members:CertUpdate script
    }

    protected function updateMember($member_data)
    {
        $member = Account::find($member_data['cid']);
        $member->name_first = $member_data['name_first'];
        $member->name_last = $member_data['name_last'];
        $member->email = $member_data['email'];
        $member->save();

        try {
            $member->addState(State::findByCode('DIVISION'), 'EUR', 'GBR');
        } catch (DuplicateStateException $e) {
            // TODO: Something.
        }
    }

    protected function getMemberIdAndEmail()
    {
        return DB::table('mship_account')
                 ->pluck('email', 'id');
    }
}
