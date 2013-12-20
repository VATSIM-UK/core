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
            $member["rating_pilot"] = Vatsim::factory("autotools")->helper_convertPilotRating($member["rating_pilot"]);
            
            // process!!
            Minion_CLI::write("Processing account ".$member["cid"]);
            $_m = ORM::factory("Account", $cid);
            Minion_CLI::write("\t-Email: ".$member["email"]);
            try {
                $_m->action_update_from_remote($member);
                Minion_CLI::write("\t-Processed successfully!");
                $membersProcessed[] = $cid;
            } catch(ORM_Validation_Exception $e){
                print_r($e->errors()); exit();
            } catch(Exception $e){
                Minion_CLI::write("\t-Error, skipping!");
            }
        }
        
        print "\n\nFinished processing.\n\n";
        
        // Log the finish.
        Log::instance()->add(Log::INFO, "Task::Cert::Memberdownload finished.");
    }
}