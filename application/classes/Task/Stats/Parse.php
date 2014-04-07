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
        
        // Define the update time so it's static for everyone!
        $updateTime = gmdate("Y-m-d H:i:s");
        
        // Download the atc from the stats file.
        //Vatsim::factory("Datafeed")->download_voice_rooms();
        $atc = Vatsim::factory("Datafeed")->feed_download("atc");
        
        // Handle them all!
        foreach($atc as $a){
            // Does it already exist?
            $session = ORM::factory("Stats_Controller");
            $session = $session->find_recent(Vatsim::factory("Datafeed")->client_get_info($a, "cid"), Vatsim::factory("Datafeed")->client_get_info($a, "callsign"));
            
            // Update some values in the session!
            $session->frequency = Vatsim::factory("Datafeed")->client_get_info($a, "frequency");
            $session->latitude = Vatsim::factory("Datafeed")->client_get_info($a, "latitude");
            $session->longitude = Vatsim::factory("Datafeed")->client_get_info($a, "longitude");
            $session->visual_range = Vatsim::factory("Datafeed")->client_get_info($a, "visualrange");
            $session->server = Vatsim::factory("Datafeed")->client_get_info($a, "server");

            if(!$session->loaded()){
                $session->account_id = Vatsim::factory("Datafeed")->client_get_info($a, "cid");
                $session->callsign = Vatsim::factory("Datafeed")->client_get_info($a, "callsign");
                $session->logon_time = Vatsim::factory("Datafeed")->helper_convert_datestamp(Vatsim::factory("Datafeed")->client_get_info($a, "time_logon"));
            }

            $session->updated_time = $updateTime;
            $session->logoff_time = NULL;
            $session->save();
        }
        
        // Let's run the pilot updates!
        $pilots = Vatsim::factory("Datafeed")->feed_download("pilot");
        
        // Handle them all and process accordingly!
        foreach($pilots as $p){
            //-----FIRSTLY, THEIR SESSION-----//
            // Does it already exist?
            $session = ORM::factory("Stats_Pilot");
            $session = $session->where("account_id", "=", Vatsim::factory("Datafeed")->client_get_info($p, "cid"));
            $session = $session->where("callsign", "LIKE", Vatsim::factory("Datafeed")->client_get_info($p, "callsign"));
            $session = $session->where("logoff_time", "IS", NULL);
            $session = $session->find();
            
            // Update the planned cruise altitude.
            $session->cruise = Vatsim::factory("Datafeed")->client_get_info($p, "planned_altitude");
            
            if(!$session->loaded()){
                $session->account_id = Vatsim::factory("Datafeed")->client_get_info($p, "cid");
                $session->callsign = Vatsim::factory("Datafeed")->client_get_info($p, "callsign");
                $session->departure = Vatsim::factory("Datafeed")->client_get_info($p, "planned_depairport");
                $session->arrival = Vatsim::factory("Datafeed")->client_get_info($p, "planned_destairport");
                $session->alternative = Vatsim::factory("Datafeed")->client_get_info($p, "planned_altairport");
                $session->logon_time = Vatsim::factory("Datafeed")->helper_convert_datestamp(Vatsim::factory("Datafeed")->client_get_info($p, "time_logon"));
            }

            $session->updated_time = $updateTime;
            $session->logoff_time = NULL;
            $session->save();
            
            //-----SECONDLY, THEIR POSREP-----//
            // Calculate the pilot's vertical speed!
            $vertical_speed = 0;
            $posrepLast = $session->posreps->order_by("timestamp", "DESC")->limit(1)->find();
            if($posrepLast->loaded()){
                $vertical_speed = $posrepLast->altitude-Vatsim::factory("Datafeed")->client_get_info($p, "altitude");
                $seconds_between_updates = strtotime($posrepLast->timestamp)-strtotime($updateTime);
                $vertical_speed = $vertical_speed / ($seconds_between_updates/60);
            }
            
            // Store the posrep!
            $posrep = ORM::factory("Stats_Pilot_Posrep");
            $posrep->pilot_session_id = $session->id;
            $posrep->latitude = Vatsim::factory("Datafeed")->client_get_info($p, "latitude");
            $posrep->longitude = Vatsim::factory("Datafeed")->client_get_info($p, "longitude");
            $posrep->altitude = Vatsim::factory("Datafeed")->client_get_info($p, "altitude");
            $posrep->vertical_speed = $vertical_speed;
            $posrep->groundspeed = Vatsim::factory("Datafeed")->client_get_info($p, "groundspeed");
            $posrep->transponder = Vatsim::factory("Datafeed")->client_get_info($p, "transponder");
            $posrep->heading = Vatsim::factory("Datafeed")->client_get_info($p, "heading");
            $posrep->qnh_mb = Vatsim::factory("Datafeed")->client_get_info($p, "QNH_Mb");
            $posrep->status = $session->calculate_status();
            $posrep->timestamp = $updateTime;
            $posrep->save();
            
            // Now go back and update the session!
            $session->status = $posrep->status;
            $session->posrep_count = $session->posrep_count+1;
            $session->posrep_latest = $posrep->id;
            $session->save();
        }
        
        // Now expire any none-updated controllers and pilots!
        ORM::factory("Stats_Controller")->run_expiration($updateTime);
        ORM::factory("Stats_Pilot")->run_expiration($updateTime);
        
        // Log the finish.
        Log::instance()->add(Log::INFO, "Task::Stats::Parse finished.");
    }
}