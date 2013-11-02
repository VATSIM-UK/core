<?php

defined('SYSPATH') or die('No direct script access.');

class Helper_Account {
    /**
     * This helper formats the name of a person to conform with expected output.
     * 
     * @param string $name The name to format.
     * @param string $type 'f' for forename, 's' for surname.
     * @return string The formatted name.
     */
    public static function formatName($name, $type = 'f') {
        //Firstname
        if ($type == 'f') {

            $name = trim($name);
            $name = ucfirst(strtolower($name));
            $name = addslashes($name);

            return $name;

            ///Surname
        } elseif ($type == 's') {

            $name = trim($name);

            ///Test for spaces- eg Le Bargy
            $space = explode(' ', $name);
            if (count($space) > 1) {

                $name = '';
                foreach ($space as $k => $v) {
                    $name .= ucfirst(strtolower($v)) . ' ';
                }

                $name = addslashes(trim($name));
                return $name;
            } else {
                if (strlen($name) <= 2) {
                    return $name;
                }

                ///Check for Mc - eg McTighe
                $name = strtolower($name);
                $first_two = $name{0} . (isset($name{1}) ? $name{1} : "");
                $therest = '';

                if ($first_two == 'mc') {
                    for ($i = 2; $i < strlen($name); $i++) {
                        $therest .= $name{$i};
                    }

                    $name = "Mc" . ucfirst($therest);
                    $name = addslashes(trim($name));
                    return $name;
                } else {

                    ///Check for hyphon seperated surnames
                    $hyphon = explode('-', $name);
                    if (count($hyphon) > 1) {

                        $name = '';
                        $numh = 0;
                        foreach ($hyphon as $k => $v) {

                            $numh = $numh + 1;
                            $name .= ucfirst(strtolower($v));

                            ///Dont append extra -
                            if ($numh != count($hyphon)) {
                                $name .= '-';
                            }
                        }

                        $name = addslashes(trim($name));
                        return $name;
                    } else {
                        ///Any other surname
                        $name = ucfirst(strtolower($name));
                        $name = addslashes(trim($name));
                        return $name;
                    }
                }
            }
        } else {
            return '';
        }
    }
    
    /**
     * Update the account using the remote VATSIM feeds.
     * 
     * @param int $account_id The account_ID we're creating/updating.
     * @return boolean True on success, false otherwise.
     */
    public static function update_using_remote($account_id){
        // Got a user to do this on?
        //if($account_id == Kohana::$config->load("general")->get("system_user") || $account_id == null || !is_numeric($account_id)){
        if($account_id == null || !is_numeric($account_id)){
            return false;
        }
        
        // Now get all of the details from VATSIM
        try {
            // Details from remote.
            $details = Vatsim::factory("autotools")->getInfo($account_id);
        
            // Valid?
            if(!is_array($details) || count($details) < 1){
                return false;
            }
            
            // Let's now run the updates!
            Helper_Account_Main::run_updates($account_id, $details);
        } catch(Exception $e){
            // TODO: Handle this!
            return false;
        }
        return true;
    }
}

?>