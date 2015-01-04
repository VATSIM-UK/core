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

class KohanaUpgrade extends aCommand {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'Kohana:Upgrade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upgrade data from old Kohana System.';

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
        $this->upgradeAccounts();
    }

    private function upgradeAccounts(){
        $kohanaUsers = DB::table("kohana_mship_account")->get();

        foreach ($kohanaUsers as $key => $m) {
            print "#" . $key . " Processing " . str_pad($m->account_id, 9, " ", STR_PAD_RIGHT) . "\t";

            DB::beginTransaction();
            print "\tDB::beginTransaction\n";
            try {
                // We need to load the XML data so that the data is correct.
                $xmlData = \VatsimXML::getData($kohanaUsers->id);

                // Create a member with this data.
                $member = Account::findOrNew($m->account_id);
                $member->account_id = $xmlData->id;
                $member->name_first = $xmlData->name_first;
                $member->name_last = $xmlData->name_last;
                $member->last_login = $xmlData->last_login;
                $member->last_login_ip = $xmlData->last_login_ip;
                $member->joined_at = $xmlData->regdate;
                $member->created_at = $m->created;
                $member->cert_checked_at = \Carbon\Carbon::now("UTC")->toDateTimeString();
                $member->status = $m->status;
                $member->save();
            } catch (Exception $e) {
                DB::rollback();
                print "\tDB::rollback\n";
                print "\tError: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile() . "\n";
                continue;
            } finally {
                print "\t" . str_repeat("-", 89) . "\n";
                DB::commit();
                print "\tDB::commit\n";
            }

            print "\n";
        }
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
