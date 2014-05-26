<?php

defined('SYSPATH') OR die('No direct access allowed.');

/**
 * VATSIM Interfacing library, for CERT, Auto-Tools and other VS data services.
 *
 * @package    Kohana/Vatsim
 * @author     Anthony Lawrence <freelancer@anthonylawrence.me.uk>
 * @copyright  (c) 2013
 * @license    http://kohanaframework.org/license
 */
class Vatsim_Autotools extends Vatsim {

    private $_actionDefault = "auths";
    private $_actions = array("auths" => "text", "email" => "text", "divdb" => "text", "regdb" => "text",
                              "ratch" => "text", "xstat" => "xml");
    private $_remoteInfoFields = array("name_first", "name_last", "rating", "regdate", "pilotrating", "region", "division");

    public function URICreate($action, $data = array()) {
        // Select the config entry corresponding to he action
        $uri = $this->_config->get("autotools_url_" . $action);
        
        // First let's add some variables to the URL!
        $uri = sprintf($uri, Arr::get($data, 0, null), Arr::get($data, 1, null));

        // keep looping through this uri replacing config variables in curly brackets {example}
        while (preg_match("/\{(.*?)\}/i", $uri, $matches)) {
            // Matches?
            if (count($matches) < 2) {
                break;
            }

            // No config value for this variable?
            if ($this->_config->get($matches[1]) == null) {
                // Check the database?
                if(ORM::factory("Setting")->getValue($matches[1]) != null){
                    $uri = preg_replace("/\{" . $matches[1] . "\}/i", urlencode(ORM::factory("Setting")->getValue($matches[1])), $uri);
                } else {
                    $uri = preg_replace("/\{" . $matches[1] . "\}/i", "UNKNOWN", $uri);
                }
            } else {
                // config value found - replace 
                $uri = str_replace($matches[0], $this->_config->get($matches[1]), $uri);
            }
        }

        // add provided variables to the URI
        return $uri;
    }

    public function downloadDatabase($type = "div") {
        // Default response
        $response = array();

        // Let's create the URL.
        $uri = $this->URICreate($type . "db");
        
        // Is this URI in the cache still?
        $fileContents = array();
        if (Cache::instance()->get("vatsim.autotools." . md5($uri), null) != null) {
            $fileContents = Cache::instance()->get("vatsim.autotools." . md5($uri));
            $fileContents = Encrypt::instance("tripledes")->decode($fileContents);
        } else {
            // Break up the query string!
            $_qs = explode("?", $uri);
            $_qs = explode("&", $_qs[1]);
            $_qsN = array();
            foreach($_qs as $v){
                $_qsN[] = explode("=", $v);
            }
            
            // Let's fetch the file!
            $request = Request::factory($uri);
            foreach($_qsN as $v){
                $request->query($v[0], $v[1]);
            }
            $request = $request->execute();
            
            // If it was a bad request, return default.
            if(!in_array($request->status(), array(200, 301, 302))){
                die("BAD RESPONSE:".$request->status());
                return $response;
            }
            
            // Let's download the file contents
            $fileContents = $request->body();
            
            // Let's save an encrypted copy of the raw contents.
            Cache::instance()->set("vatsim.autotools." . md5($uri), Encrypt::instance("tripledes")->encode($fileContents), 3600*12); // Cache for 12 hours.
        }
        

        // Now, let's process it line by line!
        foreach (explode("\n", $fileContents) as $line) {
            // Default the member.
            $member = array();
            
            // If it's an empty line, forget it!
            if(trim($line) == ""){
                continue;
            }
            
            // Now split into the array!
            list($member["cid"], $member["rating_atc"], $member["rating_pilot"],
                 $member["name_first"], $member["name_last"],
                 $member["email"], $member["age"],
                 $member["location_state"], $member["country"],
                 $member["experience"], $member["suspended_until"],
                 $member["regdate"], $member["region"],
                 $member["division"],) = explode(",", $line);
                    
            // Store!
            $response[$member["cid"]] = $member;
        }
        
        // Return all members!
        return $response;
    }

    public function authenticate($cid, $pass) {
        // Get the result
        $result = $this->runQuery("auths", array($cid, $pass));

        // Return if right/wrong/etc.
        return ($result[0] == "1");
    }

    public function confirm_email($cid, $email) {
        // Get the result
        $result = $this->runQuery("email", array($cid, $email));

        // Return if right/wrong/etc.
        return (strcasecmp($result[0], "YES") == 0);
    }

    public function getInfo($cid) {
        // Get the result
        $result = $this->runQuery("xstat", array($cid));

        // False?
        if (!$result) {
            return array();
        }
        $result_raw = get_object_vars($result->user);

        // Format!
        $result = array();
        $result["name_last"] = Arr::get($result_raw, "name_last", null);
        $result["name_first"] = Arr::get($result_raw, "name_first", null);
        $result["rating_pilot"] = $this->helper_convertPilotRating(Arr::get($result_raw, "pilotrating", "0"));
        $result["rating_atc"] = Arr::get($result_raw, "rating", "1");
        $result["country"] = Arr::get($result_raw, "country", null);
        $result["regdate"] = Arr::get($result_raw, "regdate", null);
        $result["region"] = Arr::get($result_raw, "region", null);
        $result["division"] = Arr::get($result_raw, "division", null);

        // Return the result!
        return $result;
    }

    private function runQuery($action, $data) {
        // Valid action?
        if (!array_key_exists($action, $this->_actions)) {
            $action = $this->_actionDefault;
        }

        // Get the actual (data) type and run THAT query.
        $type = $this->_actions[$action];
        return $this->{"runQuery" . ucfirst($type)}($action, $data);
    }
    
    private function runQueryCall($action, $data){
        // Construct the URI.
        $uri = $this->URICreate($action, $data);

        // Run the request.
        try {
            $request = Request::factory($uri);
            $request->client()->options(array(CURLOPT_TIMEOUT => 7, CURLOPT_SSL_VERIFYPEER=>false));
            $response = $request->execute();
        } catch(Exception $e){
            print $e->getMessage();
            die();
        }


        // Check the status!
        if ($response->status() != 200 && $response->status() != 302 && $response->status() != 301) {
            throw new Kohana_Exception("404 Result");
            return false;
        }
        
        return $response->body();
    }

    private function runQueryText($action, $data) {
        // Run the request.
        $request = $this->runQueryCall($action, $data);
        
        if(!$request){
            return false;
        }

        // Get all of the details!
        return explode("\n", $request->body());
    }

    private function runQueryXml($action, $data) {
        // Run the request.
        $request = $this->runQueryCall($action, $data);
        
        if(!$request){
            return false;
        }
        
        // Return the XML file.
        return simplexml_load_string($request);
    }
    
    
    /// HELPERS ///
    public function helper_convertPilotRating($prating){
        // Let's go through the motions! First, set the PR array
        $_pratings = array("P1" => 1,   //000001
                           "P2" => 2,   //000010
                           "P3" => 4,   //000100
                           "P4" => 8,   //001000
                           "P5" => 16,  //010000
                           "P6" => 32); //100000
        $_pratingsGained = array();
        
        // now, loop through each rating checking the bitmask.
        foreach($_pratings as $name => $value){
            // Do we have a match?
            $has = $value & $prating;
            if($has == $value){
                $_pratingsGained[] = array($name, $value);
            } else if($value > $prating){
                break;
            }
        }
        
        // RETURN THE RESULT!
        return $_pratingsGained;
    }

}

// End Vatsim