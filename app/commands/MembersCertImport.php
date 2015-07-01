<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Models\Mship\Account;
use Models\Mship\Account\Email;
use Models\Mship\Account\State;
use Models\Mship\Qualification as QualificationData;
use Models\Mship\Account\Qualification;

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
                         ->lists('account_id', 'account_id');
        $member_email_list = DB::table('mship_account_email')
                               ->where('is_primary', 1)
                               ->lists('email', 'account_id');

        $this->output('Member list and email list obtained successfully.');

        // get cert data file
        $certURL = 'https://cert.vatsim.net/vatsimnet/admin/divdbfullwpilot.php?';
        $certURL.= 'authid=' . $_ENV['vatsim.cert.at.user'].'&';
        $certURL.= 'authpassword=' . urlencode($_ENV['vatsim.cert.at.pass']).'&';
        $certURL.= 'div='.$_ENV['vatsim.cert.at.div'].'&';
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
        $member->setCertStatus($member_data[1]);
        $member->save();
        $member->addEmail($member_data[5], true, true);
        $member->addQualification(QualificationData::parseVatsimATCQualification($member_data[1]));
        $member->determineState($member_data[12], $member_data[13]);
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
