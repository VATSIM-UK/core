<?php 

defined('SYSPATH') or die('No direct script access.');

class Helper_Account_Main {
    const SESSION_CID = "sso_cid";
    private static $CHANGES_FIELD_LIST = array(
        "name_first", "name_last", "gender", "age", "created",
    );
    
    /**
     * Check whether a member is currently logged in or not.
     * 
     * @param boolean $returnID If set to true, the ID will be returned if found.
     * @return boolean|int The current ID or true on success, false otherwise.
     */
    public static function check_login_status($returnID=true){
        // Is an override set?
        if(defined("AUTH_OVERRIDE")){
            if($returnID){
                return Kohana::$config->load('general')->get("system_user");
            } else{
                return true;
            }
        }
        
        if($returnID){
            return Session::instance()->get(self::SESSION_CID, false);
        } else{
            return Session::instance()->get(self::SESSION_CID, null) == null;
        }
    }
    
    /**
     * Handle any changes in basic account details.
     * 
     * <pre>
     * Allowed values in the data array, are:
     * - name_first: first name.
     * - name_last: last name.
     * - regdate: date of account registration.
     * - rating: current rating, integer.
     * - pilotrating: current pilot rating in bitmask.
     * - country: of residence.
     * </p>
     * 
     * @param int $account_id The account_id we're creating/updating.
     * @param array $data The array of data that can be used
     */
    public static function run_updates($account_id, $data){
        // Load the account!
        $account = ORM::factory("Account_Main", $account_id);
        
        // If not loaded, set the id!
        if(!$account->loaded()){
            $account->id = $account_id;
        }
        // Go through the various fields we can update.
        foreach ($account->list_columns() as $_col => $_data) {
            if(strcasecmp($account->{$_col}, Arr::get($data, $_col)) != 0 && in_array($_col, self::$CHANGES_FIELD_LIST)){
                $account->{$_col} = Arr::get($data, $_col, $account->{$_col});
            }
        }
        
        // Save it, yeeee-ha!
        try {
            $account->checked = gmdate("Y-m-d H:i:s");
            $account->save();
        } catch(Exception $e){
            // TODO: Handle this!
            return false;
        }
        // Determine (and log) changed values.
        $changed = $account->changed();
        print "<pre>" . print_r($changed, true); exit();
        foreach ($changed as $key => $value) {
            // Add a note to the members account detailing the changes.
            Helper_Membership_Account::loadMember($account_id);
            if($key == "age"){
                Helper_Membership_Account::addNote("ACCOUNT/DETAILS_CHANGED", array(
                    $key,
                    Enum_Account_Age::getDescription($value["old"]),
                    Enum_Account_Age::getDescription($value["new"]),
                ));
            } else {
                Helper_Membership_Account::addNote("ACCOUNT/DETAILS_CHANGED", array($key, $value["old"], $value["new"]));
            }
        }

        return $account->saved();
    }
    
    /**
     * This helper formats the name of a person to conform with expected output.
     * 
     * @param string $name The name to format.
     * @param string $type 'f' for forename, 's' for surname.
     * @return string The formatted name.
     */
    public static function name_formatter($name, $type='f'){
        //Firstname
        if($type == 'f'){

            $name = trim($name);
            $name = ucfirst(strtolower($name));
            $name = addslashes($name);

            return $name;

        ///Surname
        } elseif($type == 's') {

            $name = trim($name);

            ///Test for spaces- eg Le Bargy
            $space = explode(' ', $name);
            if(count($space) > 1){

                $name = '';
                foreach($space as $k => $v){
                    $name .= ucfirst(strtolower($v)).' ';
                }

                $name = addslashes(trim($name));
                return $name;

            } else {
                if(strlen($name) <= 2){
                    return $name;
                }
                
                ///Check for Mc - eg McTighe
                $name = strtolower($name);
                            $first_two = $name{0} . (isset($name{1}) ? $name{1} : "");
                $therest = '';

                if($first_two == 'mc'){
                    for($i = 2; $i < strlen($name); $i++){
                        $therest .= $name{$i};
                    }

                    $name = "Mc".ucfirst($therest);
                    $name = addslashes(trim($name));
                    return $name;

                } else {

                    ///Check for hyphon seperated surnames
                    $hyphon = explode('-', $name);
                    if(count($hyphon) > 1){

                        $name = '';
                        $numh = 0;
                        foreach($hyphon as $k => $v){

                            $numh = $numh+1;
                            $name .= ucfirst(strtolower($v));

                            ///Dont append extra -
                            if($numh != count($hyphon)){
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
}