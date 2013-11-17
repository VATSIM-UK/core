<?php defined('SYSPATH') or die('No direct script access.');
 
class Task_Postmaster_Dispatch extends Minion_Task
{
    protected $_options = array(
        "debug" => false,
    );
    
    protected function _execute(array $params)
    {
        // Disable so output is instant & log starting.
        ob_end_flush();
        Log::instance()->add(Log::INFO, "Task::Postmaster::Dispatch started.");
        
        if(Arr::get($params, "debug")) print "Dispatching all elegible emails from the queue...\n\n";
        
        // Get all emails, we'll order by priority!
        $emails = ORM::factory("Postmaster_Queue")
                     ->where("status", "=", Enum_System_Postmaster_Queue_Status::PARSED)
                     ->or_where("status", "=", Enum_System_Postmaster_Queue_Status::DELAYED)
                     ->order_by("priority", "DESC")->find_all();
        if(Arr::get($params, "debug")) print "There are ".count($emails)." waiting to be dispatched:\n";
        
        // Now parse them all
        foreach($emails as $email){
            $email->action_dispatch();
            if(Arr::get($params, "debug")) print "\t[QID: ".$email->id."/P".$email->priority."] ".$email->subject." [DISPATCHED TO ".$email->recipient_id."]\n";
        }
        
        // Log the finish.
        Log::instance()->add(Log::INFO, "Task::Postmaster::Dispatch finished.");
    }
}