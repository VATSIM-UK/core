<?php

defined('SYSPATH') OR die('No direct access allowed.');

/**
 * VATSIM Interfacing library for the data feed.
 *
 * @package    Kohana/Vatsim
 * @author     Anthony Lawrence <freelancer@anthonylawrence.me.uk>
 * @copyright  (c) 2013
 * @license    http://kohanaframework.org/license
 */
class Vatsim_Datafeed extends Vatsim {

    private $_statusFile = "http://status.vatsim.net/status.txt";
    private $_dataFile = "";
    private $_servers = array();
    private $_headers = array("clients" => array('callsign', 'cid', 'realname', 'clienttype', 'frequency', 'latitude', 'longitude', 'altitude', 'groundspeed', 'planned_aircraft',
            'planned_tascruise', 'planned_depairport', 'planned_altitude', 'planned_destairport', 'server', 'protrevision', 'rating', 'transponder', 'facilitytype',
            'visualrange', 'planned_revision', 'planned_flighttype', 'planned_deptime', 'planned_actdeptime', 'planned_hrsenroute', 'planned_minenroute',
            'planned_hrsfuel', 'planned_minfuel', 'planned_altairport', 'planned_remarks', 'planned_route', 'planned_depairport_lat', 'planned_depairport_lon',
            'planned_destairport_lat', 'planned_destairport_lon', 'atis_message', 'time_last_atis_received', 'time_logon', 'heading', 'QNH_iHg', 'QNH_Mb',""
    ));

    public function servers_fetch() {
        // Get the status.txt file containing a list of all available servers.
        if (Cache::instance()->get("vatsim.status.txt", null) != null) {
            $fileContents = unserialize(Cache::instance()->get("vatsim.status.txt"));
        } else {
            $remoteFile = Request::factory($this->_statusFile)->execute();

            if (!in_array($remoteFile->status(), array(200))) {
                return false;
            }

            $fileContents = $remoteFile->body();

            // Now process the file contents and store the servers locally.
            preg_match_all("/url0=(.*)/i", $fileContents, $matches);
            $fileContents = isset($matches[1]) ? $matches[1] : array();

            Cache::instance()->set("vatsim.status.txt", serialize($fileContents), 3600 * 12); // Cache for 12 hours.
        }

        $this->_servers = $fileContents;

        $this->servers_choose();
    }

    public function servers_choose() {
        $this->_dataFile = trim($this->_servers[array_rand($this->_servers)]);
    }

    public function feed_download($type="pilot") {
        // First we need to make sure a server has been chosen!
        if($this->_dataFile == ""){
            $this->servers_fetch();
        }

        // Now fetch the file - if it's not cached!
        if (Cache::instance()->get("vatsim.data.".strtolower($type).".txt", null) != null) {
            $fileContents = unserialize(Cache::instance()->get("vatsim.data.".strtolower($type).".txt"));
        } else {
            $remoteFile = Request::factory($this->_dataFile)->execute();

            if (!in_array($remoteFile->status(), array(200))) {
                return false;
            }

            $fileContents = $remoteFile->body();
            
            // We *MUST* strip data from the start to the start of !CLIENTS as we don't need it!
            $strip_from = strpos($fileContents, "!CLIENTS", 1200);
            $strip_to = strpos($fileContents, "!PREFILE", $strip_from);
            $fileContents = substr($fileContents, $strip_from + 11, $strip_to-8 - $strip_from);

            // Split the data into multiple lines.
            preg_match_all("/.*:".strtoupper($type).":.*/i", $fileContents, $fileContents);
            $fileContents = array_map(function($line){
                return explode(":", $line);
            }, $fileContents[0]);
            
            Cache::instance()->set("vatsim.data.".strtolower($type).".txt", serialize($fileContents), 120); // Cache for 2 minutes.
        }
            
        return $fileContents;
    }

    public function fetch_voice_servers() {
        // First we need to make sure a server has been chosen!
        if($this->_dataFile == ""){
            $this->servers_fetch();
        }

        // Now fetch the file - if it's not cached!
        if (Cache::instance()->get("vatsim.data.voice.txt", null) != null) {
            $fileContents = unserialize(Cache::instance()->get("vatsim.data.voice.txt"));
        } else {
            $remoteFile = Request::factory($this->_dataFile)->execute();

            if (!in_array($remoteFile->status(), array(200))) {
                return false;
            }

            $fileContents = $remoteFile->body();
            
            // We *MUST* strip data from the start to the start of !VOICE SERVERS as we don't need it!
            $strip_from = strpos($fileContents, "!VOICE SERVERS", 2500);
            $strip_to = strpos($fileContents, "!CLIENTS", $strip_from);
            $fileContents = substr($fileContents, $strip_from + 15, $strip_to-8 - $strip_from);

            // Split the data into multiple lines.
            preg_match_all("/(.*?):.*/i", $fileContents, $fileContents);
            $fileContents = isset($fileContents[1]) ? $fileContents[1] : array();
            
            Cache::instance()->set("vatsim.data.voice.txt", serialize($fileContents), 3600 * 24); // Cache for 24 hours.
        }
            
        return $fileContents;
    }
    
    public function download_voice_rooms(){
        // First we need to make sure a server has been chosen!
        if($this->_dataFile == ""){
            $this->servers_fetch();
        }
        
        // We also need the voice servers!
        $voiceServers = $this->fetch_voice_servers();
        
        // Now, we're going to go over each and every voice server and gather details about the clients in those rooms.
        foreach($voiceServers as $vx){
            $remoteData = Request::factory("http://".$vx.":18009?opts=-R-D")->execute();
            $remoteData = $remoteData->body();
            
            // Let's get each section seperately.
            preg_match_all("/\<p\>(.*)\<\/p\>/m", $remoteData, $blockMatches);
            print_r($blockMatches);
        }
    }
    
    public function client_get_info($client, $key){
        return isset($client[array_search($key, $this->_headers["clients"])]) ? $client[array_search($key, $this->_headers["clients"])] : null;
    }
    
    public function helper_cleanse_route($route){
        return $route;
    }
    
    public function helper_convert_datestamp($datestamp){
        $year = substr($datestamp, 0, 4);
        $month = substr($datestamp, 4, 2);
        $day = substr($datestamp, 6, 2);
        $hour = substr($datestamp, 8, 2);
        $minute = substr($datestamp, 10, 2);
        $seconds = substr($datestamp, 12, 2);
        
        return ($year."-".$month."-".$day." ".$hour.":".$minute.":".$seconds);
    }
}
