<?php defined('SYSPATH') or die('No direct script access.');
 
class Task_Cert_Memberdownload extends Minion_Task
{
    protected $_options = array(
        "debug" => false,
    );
    
    protected function _execute(array $params)
    {
        // Disable so output is instant & log starting.
        ob_end_flush();
        Log::instance()->add(Log::INFO, "Task::Cert::Memberdownload started.");
        
        if(Arr::get($params, "debug")) print "Processing all members from the VATSIM CERTIFICATE DATABASE..\n\n";
        
        // Download the division database from CERT
        if(Arr::get($params, "debug")) print "Download the division db file ";
        $members = Vatsim::factory("autotools")->downloadDatabase("div");
        if(Arr::get($params, "debug")) print "Done.\n\n";
        
        if(Arr::get($params, "debug")) print "The file contains ".Num::format(count($members), 0)." members to process.\n\n";
        
        /** MAIN LOOP: Through each member in divdb. **/
        $count = 0;
        $membersProcessed = array();
        foreach($members as $cid => $member){
            $count++;

            // First, let's convert the pratings!
            $member["prating"] = Vatsim::factory("autotools")->helper_convertPilotRating($member["prating"]);
            
            // process!!
            Minion_CLI::write("Processing account ".$member["cid"]);
            Helper_Membership_Account::$_debug = true;
            if(Helper_Membership_Account::processMember($member, Helper_Membership_Account::ACTION_CERT)){
                Minion_CLI::write("\t-Processed successfully!");
                $membersProcessed[] = $cid;
            }
        }
        
        // Now, anyone who hasn't been processed, either needs setting to GUEST or INACTIVE.
        if(count($membersProcessed) > 0){
            Minion_CLI::write("Now processing the stragglers:");
            $_membersUnprocessed = ORM::factory("Account")->where("id", ">", 810000)->where("id", "NOT IN", $membersProcessed)->find_all();
            foreach($_membersUnprocessed as $member){
                // Let's get their info, first.
                Minion_CLI::write("-".$member->id.":");
                $info = Vatsim::factory("autotools")->getInfo($member->id);

                // If they're rating -1, they're just inactive!
                Minion_CLI::write("\t-Are they just a guest or inactive?");
                if($info["rating_atc"] == -1){
                    Minion_CLI::write("\t\t-INACTIVE");
                    Helper_Membership_Account::loadMember($member->id);
                    Helper_Membership_Account::processMember(array("status" => Enum_Account::STATUS_INACTIVE), Helper_Membership_Account::ACTION_CERT);
                } else { // Otherwise, guest them!
                    Minion_CLI::write("\t\t-GUEST");
                    Helper_Membership_Account::loadMember($member->id);
                    Helper_Membership_Account::processMember(array("state" => Enum_Account_State::GUEST), Helper_Membership_Account::ACTION_CERT);
                }
            }
        }
        
        print "\n\nFinished processing.\n\n";
        
        // Log the finish.
        Log::instance()->add(Log::INFO, "Task::Cert::Memberdownload finished.");
    }
}