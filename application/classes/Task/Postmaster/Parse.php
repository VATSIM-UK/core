<?php defined('SYSPATH') or die('No direct script access.');
 
class Task_Postmaster_Parse extends Minion_Task
{
    protected $_options = array(
        "debug" => false,
    );
    
    protected function _execute(array $params)
    {
        // Disable so output is instant & log starting.
        ob_end_flush();
        Log::instance()->add(Log::INFO, "Task::Postmaster::Parse started.");
        
        if(Arr::get($params, "debug")) print "Parsing all emails that are currently in the queue...\n\n";
        
        // Get all NEW emails.
        $emails = ORM::factory("Postmaster_Queue")->where("status", "=", Enum_System_Postmaster_Queue_Status::QUEUED)->find_all();
        if(Arr::get($params, "debug")) print "There are ".count($emails)." waiting to be parsed:";
        
        // Now parse them all
        foreach($emails as $email){
            $email->action_parse();
            if(Arr::get($params, "debug")) print "\t[QID: ".$email->id."] ".$email->subject."\n";
        }
        
        // Log the finish.
        Log::instance()->add(Log::INFO, "Task::Postmaster::Parse finished.");
    }
}