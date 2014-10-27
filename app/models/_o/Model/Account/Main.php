<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Account_Main extends Model_Master {
    // Validation rules
    public function rules() {
        return array(
            'id' => array(
                array('min_length', array(':value', 6)),
                array('max_length', array(':value', 7)),
                array('numeric'),
            ),
            'name_first' => array(
                array('not_empty'),
            ),
            'name_last' => array(
                array('not_empty'),
            ),
            /*'gender' => array(
                array("regex", array(":value", "/(M|F)/i")),
            ),*/
        );
    }

    /**
     * @override
     */
    public function save(\Validation $validation = NULL) {
        // Get the old values!
        $ovs = $this->changed();

        parent::save($validation);

        // Basic logs!
        $logKeys = array("name_first", "name_last", "status");
        foreach ($ovs as $key => $value) {
            if (in_array($key, $logKeys)) {
                $data = array();
                $data[] = $key;
                if ($key == "status") {
                    $data[] = Enum_Account_Status::getDescription(decbin($value["old"]));
                    $data[] = Enum_Account_Status::getDescription(decbin($value["new"]));
                } else {
                    $data[] = $value["old"];
                    $data[] = $value["new"];
                }
                ORM::factory("Account_Note")->writeNote($this, "ACCOUNT/DETAILS_CHANGED", 707070, $data);
            }
        }

        return true;
    }
    
    /**
     * Create/Update a user account from a remote source.
     * 
     * @param array $data If set, this data will be used as the "remote" data.
     */
    public function data_from_remote($data=null){
        if(!$this->loaded()){
            $newAccount = true;
        } else {
            $newAccount = false;
        }
        
        // If this is a system account, ignore it!
        if ($this->isSystem()) {
            return false;
        }
        
        // What are our data source(s) for this?
        if($data == null){
            $details = Vatsim::factory("autotools")->getInfo($this->id);
        } else {
            $details = $data;
        }
        
        // Basic details (name, age, location, etc).
        $this->setName(Arr::get($details, "name_first", NULL), Arr::get($details, "name_last", NULL), false);
        
        // Emails!
        // A note about these: We'll *always* treat the email in our data feed as primary.  If an email is passed to this function **IT BECOMES THE PRIMARY ONE**.
        if (Arr::get($details, "email", null) != null) {
            $this->emails->action_add_email($this, $details["email"], 1, 1);
        }
        
        // We need to add the OBS date of a member to the qualifications table, when they are created.
        if (!$this->qualifications->check_has_qualification("atc", 1)) {
            $this->qualifications->addATCQualification($this, 1, $details['regdate']); // Add OBS to date they joined.
        }
        
        // Qualifications!
        if (Arr::get($details, "rating_atc", null) != null) {
            $this->qualifications->addATCQualification($this, Arr::get($details, "rating_atc", 1));
        }
        // Let's also just run a check for the previous rating too!
        $prat = Vatsim::factory("autotools")->getPreviousRating($this->id);
        if($prat !== false){
            $this->qualifications->addATCQualification($this, $prat);
        }
        
        // Pilot ratings are slightly funny in that we need to set each one!
        if (Arr::get($details, "rating_pilot", null) != null && is_array($details["rating_pilot"])) {
            foreach ($details["rating_pilot"] as $prating) {
                $this->qualifications->addPilotQualification($this, Enum_Account_Qualification_Pilot::IdToValue($prating[0]), NULL);
            }
        }
        
        // Status?
        if (Arr::get($details, "rating_atc", 99) < 1) {
            if (Arr::get($details, "rating_atc", 99) == 0) {
                $this->setStatus(Enum_Account_Status::INACTIVE, true);
            } else {
                $this->unSetStatus(Enum_Account_Status::INACTIVE, true);
            }
            if (Arr::get($details, "rating_atc", 99) == -1) {
                $this->setStatus(Enum_Account_Status::NETWORK_SUSPENDED, true);
            } else {
                $this->unSetStatus(Enum_Account_Status::NETWORK_SUSPENDED, true);
            }
        } else {
            $this->unSetStatus(Enum_Account_Status::INACTIVE, true);
            $this->unSetStatus(Enum_Account_Status::NETWORK_SUSPENDED, true);
        }
        
        // Work out what the state is!
        if (Arr::get($details, "division", null) != null && strcasecmp($details["division"], "GBR") == 0) {
            $this->states->addState($this, "DIVISION");
            
            // Since they're division, is this their first time?
            if(!$this->states->checkPrevious($this->states->getCurrent()->state)){
                ORM::factory("Postmaster_Queue")->action_add("NEW_DIVISON_MEM", $this->id, null, array());
            }
            
        } elseif (Arr::get($details, "region", null) != null && strcasecmp($details["region"], "EUR") == 0) {
            $this->states->addState($this, "REGION");
        } else {
            $this->states->addState($this, "INTERNATIONAL");
        }
        
        // What are our data source(s) for this?
        if($data == null){
            ORM::factory("Account_Note")->writeNote($this, "ACCOUNT/AUTO_CERT_UPDATE_XML", 707070, array(), Enum_Account_Note_Type::SYSTEM);
        } else {
            ORM::factory("Account_Note")->writeNote($this, "ACCOUNT/AUTO_CERT_UPDATE", 707070, array(), Enum_Account_Note_Type::SYSTEM);
        }
        
        if($newAccount){
            // If this is their first SSO login (i.e. no IP address), welcome them!
            ORM::factory("Postmaster_Queue")->action_add("SSO_CREATED", $this->id, null, array(
                "account_state" => $this->getStatus(). " " .$this->states->getCurrent()->formatState(true),
                "primary_email" => $this->emails->get_active_primary(false)->email,
            ));

        }
        
        $this->checked = gmdate("Y-m-d H:i:s");
        $this->save();
    }

    /**
     * Count the number of times the specified {@link $ip} has been used to login,
     * within {@link $timeLimit}.
     * 
     * @param string $ip The IP to get the count for.  If left as NULL, the last will be used.
     * @param string $timeLimit An strtotime() string to determine the period we're checking.
     * @return int The number of times the {@link $ip} has been using within {@link $timeLimit}.
     */
    public function count_last_login_ip_usage($ip = null, $timeLimit = "-8 hours") {
        // Use the last IP of this account?
        if ($ip == null) {
            $ip = $this->get_last_login_ip();
        }

        $ipCheck = ORM::factory("Account_Main")->where("last_login_ip", "=", ip2long($ip));

        // Exclude this user?
        if ($this->id > 0) {
            $ipCheck = $ipCheck->where("id", "!=", $this->id);
        }

        // Limit the timeframe?
        if ($timeLimit != null && $timeLimit != false) {
            $ipCheck = $ipCheck->where("last_login", ">=", gmdate("Y-m-d H:i:s", strtotime($timeLimit)));
        }

        // Return the count.
        return $ipCheck->reset(FALSE)->count_all();
    }

    /**
     * Set session data!
     * 
     * @param boolean $quickLogin If TRUE, it will set a quickLogin session value.
     * @return void
     */
    public function setSessionData() {
        $this->session()->set(ORM::factory("Setting")->getValue("auth.account.session.key"), $this->id);

        // Cookie!
        $lifetime = strtotime("+" . ORM::factory("Setting")->getValue("auth.account.cookie.lifetime"));
        $lifetime = $lifetime - time();
        $salt = $this->renew_salt();
        $cookieValue = $this->id . "|" . $salt;
        Cookie::encrypt(ORM::factory("Setting")->getValue("auth.account.cookie.key"), $cookieValue, $lifetime);
    }

    /**
     * Destory the session data!
     * 
     * @param boolean $quickLogin If TRUE, it will set a quickLogin session value.
     * @return void
     */
    private function destroySessionData() {
        $this->session()->delete(ORM::factory("Setting")->getValue("auth.account.session.key"));
        Cookie::delete(ORM::factory("Setting")->getValue("auth.account.cookie.key"));
        $this->session()->delete("sso_quicklogin");
        $this->session()->regenerate();
    }

    /**
     * Override the current account with another account.
     */
    public function override_enable($override_id) {
        $this->session()->set("sso_account_override", $this->session()->get(ORM::factory("Setting")->getValue("auth.account.session.key")));
        $this->session()->set(ORM::factory("Setting")->getValue("auth.account.session.key"), $override_id);
    }

    /**
     * Override the current account with another account.
     */
    public function override_disable() {
        $this->session()->set(ORM::factory("Setting")->getValue("auth.account.session.key"), $this->session()->get("sso_account_override"));
        $this->session()->delete("sso_account_override");
    }

    /**
     * Check whether this account is being overriden.
     */
    public function is_overriding() {
        return !($this->session()->get("sso_account_override", null) == null);
    }
}

?>
