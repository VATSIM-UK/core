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
    protected $name = 'Members:CertImport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import/update member emails from CERT AutoTools';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        // get a list of current members and their emails
        // Note: accounts for the possibility of a member not having a (primary) email in mship_account_email
        $member_list = DB::table('mship_account')
                         ->pluck('account_id', 'account_id');
        $member_email_list = DB::table('mship_account_email')
                               ->where('is_primary', 1)
                               ->pluck('email', 'account_id');

        $this->output('Member list and email list obtained successfully.');

        // get cert data file
        $certURL = 'https://cert.vatsim.net/vatsimnet/admin/divdbfullwpilot.php?';
        $certURL.= 'authid=' . env('VATSIM_CERT_AT_USER') .'&';
        $certURL.= 'authpassword=' . urlencode(env('VATSIM_CERT_AT_PASS')).'&';
        $certURL.= 'div='.env('VATSIM_CERT_AT_DIV').'&';
        $members = file($certURL);

        foreach ($members as $member) {
            $member = str_getcsv($member, ',', '');
            $this->output("Processing {$member[0]} {$member[3]} {$member[4]}: ", false);

            // if member doesn't exist, create them, otherwise check/update their email
            if (!array_key_exists($member[0], $member_list)) {
                $this->createNewMember($member);
                $this->output('created new account');
            } else {
                $current_email = array_key_exists($member[0], $member_email_list)
                               ? $member_email_list[$member[0]]
                               : false;
                if ($current_email != $member[5]) {
                    $this->updateMemberEmail($member);
                    $this->output('updated member email');
                } else {
                    $this->output('no changes needed');
                }
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

        // If they're NONE UK and an Instructor we need their old rating.
        if(($member_data[1] != 8 AND $member_data[1] != 9) OR $member->current_state->state == \App\Models\Mship\Account\State::STATE_DIVISION) {
            $member->addQualification(QualificationData::parseVatsimATCQualification($member_data[1]));
        } else {
            // Since they're an instructor AND NONE-UK
            $_prevRat = VatsimXML::getData($member->account_id, "idstatusprat");
            if (isset($_prevRat->PreviousRatingInt)) {
                $prevAtcRating = QualificationData::parseVatsimATCQualification($_prevRat->PreviousRatingInt);
                $member->addQualification($prevAtcRating);
            }
        }

        // anything else should be processed by the Members:CertUpdate script
    }

    protected function updateMemberEmail($member_data)
    {
        $member = Account::find($member_data[0]);
        $member->addEmail($member_data[5], true, true);
    }

    protected function output($message, $eol = true)
    {
        if ($this->option('verbose')) {
            if ($eol) {
                $message .= PHP_EOL;
            }

            echo $message;
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array();
    }
}
