<?php defined('SYSPATH') or die('No direct script access.');
 
class Task_Stats_Parse extends Minion_Task
{
    protected $_options = array(
        "debug" => false,
    );
    
    protected function _execute(array $params)
    {
        // Disable so output is instant & log starting.
        ob_end_flush();
        Log::instance()->add(Log::INFO, "Task::Stats::Parse started.");
        
        if(Arr::get($params, "debug")) print "Parsing the online data feed ready for processing...\n\n";
        
        // Download the stats file.
        Vatsim::factory("Datafeed")->fetch_servers();
        
        // Log the finish.
        Log::instance()->add(Log::INFO, "Task::Stats::Parse finished.");
    }
}