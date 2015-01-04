<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Models\Mship\Account\Account;
use Models\Mship\Account\Email;
use Models\Mship\Account\State;
use Models\Mship\Account\Security;
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
        $this->upgradeMShipAccount();
        $this->upgradeMShipAccountEmail();
        $this->upgradeMShipAccountQualification();
        $this->upgradeMShipAccountSecurity();
        $this->upgradeMShipAccountState();
    }

    private function upgradeMShipAccount() {
        $kohanaUsers = DB::table("kohana_mship_account")->where("id", ">", "800000")->get();

        foreach ($kohanaUsers as $key => $m) {
            print "#" . $key . " Processing " . str_pad($m->id, 9, " ", STR_PAD_RIGHT) . "\t";

            DB::beginTransaction();
            print "\tDB::beginTransaction\n";
            try {
                // We need to load the XML data so that the data is correct.
                $xmlData = \VatsimXML::getData($m->id);

                if($xmlData->cid == ""){
                    continue;
                }

                // Create a member with this data.
                $member = Account::findOrNew($m->id);
                $member->account_id = $xmlData->cid;
                $member->name_first = $xmlData->name_first;
                $member->name_last = $xmlData->name_last;
                $member->last_login = $m->last_login;
                $member->last_login_ip = $m->last_login_ip;
                $member->joined_at = $xmlData->regdate;
                $member->created_at = $m->created;
                $member->cert_checked_at = \Carbon\Carbon::now("UTC")->toDateTimeString();
                $member->status = $m->status;
                $member->save();

                print "\tMember::Saved\n";

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


    private function upgradeMShipAccountEmail() {
        $kohanaData = DB::table("kohana_mship_account_email")->where("account_id", ">", "800000")->get();

        foreach ($kohanaData as $key => $kd) {
            print "#" . $key . " Processing " . str_pad($kd->id, 9, " ", STR_PAD_RIGHT) . "\t";

            DB::beginTransaction();
            print "\tDB::beginTransaction\n";
            try {
                $email = new Email();
                $email->account_id = $kd->account_id;
                $email->email = $kd->email;
                $email->is_primary = $kd->primary;
                $email->verified = $kd->verified;
                $email->created_at = $kd->created;
                $email->updated_at = $kd->verified ? $kd->verified : $kd->created;
                $email->deleted_at = $kd->deleted;
                $email->save();

                print "\tEmail::Saved\n";
            } catch (Exception $e) {
                DB::rollback();
                print "\tDB::rollback\n";
                print "\tError: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile() . "\n";
                \Log::error("Error: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
                continue;
            } finally {
                print "\t" . str_repeat("-", 89) . "\n";
                DB::commit();
                print "\tDB::commit\n";
            }

            print "\n";
        }
    }

    private function upgradeMShipAccountQualification() {
        $kohanaData = DB::table("kohana_mship_account_qualification")->where("account_id", ">", "800000")->where("type", "=", "ATC")->get();

        foreach ($kohanaData as $key => $kd) {
            print "#" . $key . " Processing " . str_pad($kd->id, 9, " ", STR_PAD_RIGHT) . "\t";

            DB::beginTransaction();
            print "\tDB::beginTransaction\n";
            try {
                if($kd->value == "7"){
                    $kd->value == 6;
                }

                $account = new Qualification();
                $account->account_id = $kd->account_id;
                $account->qualification_id = $kd->value;
                $account->created_at = $kd->created;
                $account->updated_at = $kd->created;
                $account->save();

                print "\tQualification::Saved\n";
            } catch (Exception $e) {
                DB::rollback();
                print "\tDB::rollback\n";
                print "\tError: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile() . "\n";
                \Log::error("Error: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
                continue;
            } finally {
                print "\t" . str_repeat("-", 89) . "\n";
                DB::commit();
                print "\tDB::commit\n";
            }

            print "\n";
        }
    }

    private function upgradeMShipAccountSecurity() {
        $kohanaData = DB::table("kohana_mship_account_security")->where("account_id", ">", "800000")->get();

        foreach ($kohanaData as $key => $kd) {
            print "#" . $key . " Processing " . str_pad($kd->id, 9, " ", STR_PAD_RIGHT) . "\t";

            DB::beginTransaction();
            print "\tDB::beginTransaction\n";
            try {
                $security = new Security();
                $security->account_id = $kd->account_id;

                if($kd->type == 10){
                    $security->security_id = 1;
                } elseif($kd->type == 20){
                    $security->security_id = 2;
                } elseif($kd->type == 50){
                    $security->security_id = 3;
                } elseif($kd->type == 100){
                    $security->security_id = 4;
                }

                $security->value = $kd->value;
                $security->created_at = $kd->created;
                if($kd->expires){
                    $security->expires_at = $kd->expires;
                }
                $security->save();

                print "\tSecurity::Saved\n";
            } catch (Exception $e) {
                DB::rollback();
                print "\tDB::rollback\n";
                print "\tError: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile() . "\n";
                \Log::error("Error: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
                continue;
            } finally {
                print "\t" . str_repeat("-", 89) . "\n";
                DB::commit();
                print "\tDB::commit\n";
            }

            print "\n";
        }
    }

    private function upgradeMShipAccountState() {
        $kohanaData = DB::table("kohana_mship_account_state")->where("account_id", ">", "800000")->get();

        foreach ($kohanaData as $key => $kd) {
            print "#" . $key . " Processing " . str_pad($kd->id, 9, " ", STR_PAD_RIGHT) . "\t";

            DB::beginTransaction();
            print "\tDB::beginTransaction\n";
            try {
                $state = new State();
                $state->account_id = $kd->account_id;
                $state->state = $kd->state;
                $state->created_at = $kd->created;
                $state->updated_at = $kd->created;
                $state->deleted_at = $kd->removed;
                $state->save();

                print "\tState::Saved\n";
            } catch (Exception $e) {
                DB::rollback();
                print "\tDB::rollback\n";
                print "\tError: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile() . "\n";
                \Log::error("Error: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
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
