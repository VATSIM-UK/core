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
        if($this->argument("function") == "all"){
            $this->upgradeMShipAccount();
            $this->upgradeMShipAccountEmail();
            $this->upgradeMShipAccountQualification();
            $this->upgradeMShipAccountSecurity();
            $this->upgradeMShipAccountState();
        } else {
            $this->{"upgrade".$this->argument("function")}();
        }
    }

    private function upgradeMShipAccount() {
        DB::table("kohana_mship_account")->where("id", ">", "800000")->chunk(1000, function($kohanaUsers) {
            $tableData = array();

            $this->info("");
            $this->info("New chunk started!");
            foreach ($kohanaUsers as $key => $m) {

                // We need to load the XML data so that the data is correct.
                $xmlData = \VatsimXML::getData($m->id);

                if ($xmlData->cid == "") {
                    continue;
                }

                // Create a member with this data.
                $member = array();
                $member["account_id"] = $xmlData->cid;
                $member["name_first"] = $xmlData->name_first;
                $member["name_last"] = $xmlData->name_last;
                $member["last_login"] = $m->last_login;
                $member["last_login_ip"] = $m->last_login_ip;
                $member["joined_at"] = $xmlData->regdate;
                $member["created_at"] = $m->created;
                $member["cert_checked_at"] = \Carbon\Carbon::now("UTC")->toDateTimeString();
                $member["status"] = $m->status;
                $tableData[] = $member;

                $this->info("Total members queued: " . count($tableData));
            }

            DB::beginTransaction();
            try {
                Account::insert($tableData);
            } catch (Exception $e) {
                DB::rollback();
                $this->error("Error: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
                \Log::info("Error: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
            } finally {
                DB::commit();
            }
        });
    }

    private function upgradeMShipAccountEmail() {

        DB::table("kohana_mship_account_email")->where("account_id", ">", "800000")->chunk(1000, function($kohanaData) {
            $tableData = array();

            $this->info("");
            $this->info("New chunk started!");
            foreach ($kohanaData as $key => $kd) {

                $email = new Email();
                $email->account_id = $kd->account_id;
                $email->email = $kd->email;
                $email->is_primary = $kd->primary;
                $email->verified = $kd->verified;
                $email->created_at = $kd->created;
                $email->updated_at = $kd->verified ? $kd->verified : $kd->created;
                $email->deleted_at = $kd->deleted;
                $email->save();

                // Create a member with this data.
                $newData = array();
                $newData["account_id"] = $kd->account_id;
                $newData["email"] = $kd->email;
                $newData["is_primary"] = $kd->primary;
                $newData["verified"] = $kd->verified;
                $newData["created_at"] = $kd->created;
                $newData["updated_at"] = $kd->verified ? $kd->verified : $kd->created;
                $newData["deleted_at"] = $kd->deleted;
                $tableData[] = $newData;

                $this->info("Total data queued: " . count($tableData));
            }

            DB::beginTransaction();
            try {
                Email::insert($tableData);
            } catch (Exception $e) {
                DB::rollback();
                $this->error("Error: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
                \Log::info("Error: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
            } finally {
                DB::commit();
            }
        });
    }

    private function upgradeMShipAccountQualification() {
        DB::table("kohana_mship_account_qualification")->where("account_id", ">", "800000")->where("type", "=", "ATC")->chunk(1000, function($kohanaData) {
            $tableData = array();

            $this->info("");
            $this->info("New chunk started!");
            foreach ($kohanaData as $key => $kd) {
                if ($kd->value == "7") {
                    $kd->value == 6;
                }

                $newData = array();
                $newData["account_id"] = $kd->account_id;
                $newData["qualification_id"] = $kd->value;
                $newData["created_at"] = $kd->created;
                $newData["updated_at"] = $kd->created;
                $tableData[] = $newData;

                $this->info("Total data queued: " . count($tableData));
            }

            DB::beginTransaction();
            try {
                Qualification::insert($tableData);
            } catch (Exception $e) {
                DB::rollback();
                $this->error("Error: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
                \Log::info("Error: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
            } finally {
                DB::commit();
            }
        });
    }

    private function upgradeMShipAccountSecurity() {
        DB::table("kohana_mship_account_security")->where("account_id", ">", "800000")->chunk(1000, function($kohanaData) {
            $tableData = array();

            $this->info("");
            $this->info("New chunk started!");
            foreach ($kohanaData as $key => $kd) {
                $newData = array();
                $newData["account_id"] = $kd->account_id;

                if ($kd->type == 10) {
                    $newData["security_id"] = 1;
                } elseif ($kd->type == 20) {
                    $newData["security_id"] = 2;
                } elseif ($kd->type == 50) {
                    $newData["security_id"] = 3;
                } elseif ($kd->type == 100) {
                    $newData["security_id"] = 4;
                }

                $newData["value"] = $kd->value;
                $newData["created_at"] = $kd->created;
                if ($kd->expires) {
                    $newData["expires_at"] = $kd->expires;
                }
                $tableData[] = $newData;

                $this->info("Total data queued: " . count($tableData));
            }

            DB::beginTransaction();
            try {
                Security::insert($tableData);
            } catch (Exception $e) {
                DB::rollback();
                $this->error("Error: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
                \Log::info("Error: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
            } finally {
                DB::commit();
            }
        });
    }

    private function upgradeMShipAccountState() {
        DB::table("kohana_mship_account_state")->where("account_id", ">", "800000")->chunk(1000, function($kohanaData) {
            $tableData = array();

            $this->info("");
            $this->info("New chunk started!");
            foreach ($kohanaData as $key => $kd) {
                $newData = array();

                $newData["account_id"] = $kd->account_id;
                $newData["state"] = $kd->state;
                $newData["created_at"] = $kd->created;
                $newData["updated_at"] = $kd->created;
                $newData["deleted_at"] = $kd->removed;

                $tableData[] = $newData;

                $this->info("Total data queued: " . count($tableData));
            }

            DB::beginTransaction();
            try {
                State::insert($tableData);
            } catch (Exception $e) {
                DB::rollback();
                $this->error("Error: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
                \Log::info("Error: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
            } finally {
                DB::commit();
            }
        });
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments() {
        return array(
            array("function", InputArgument::OPTIONAL, "The function to run on the upgrade script(s).", "all"),
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
