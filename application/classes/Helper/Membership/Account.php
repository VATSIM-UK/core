<?php

defined('SYSPATH') or die('No direct script access.');

class Helper_Membership_Account {
    // General account processing constants.
    public static $_debug = false;
    const ACTION_INSERT = 'I';
    const ACTION_UPDATE = 'U';
    const ACTION_CERT = "CERT";
    const ACTION_USER = "USER";

    // Email processing constants.
    const ACTION_EMAIL_CREATE = 'ACT_EM_CRE'; // Create a new email address.
    const ACTION_EMAIL_CREATE_PRIMARY = 'ACT_EM_CRE_PRI'; // Create a new primary email address.
    const ACTION_EMAIL_PRIMARY_DELETE = 'ACT_EM_PRI_DEL'; // Add new primary and delete old.
    const ACTION_EMAIL_PRIMARY_DEMOTE = 'ACT_EM_PRI_DEM'; // Add new primary and demote old.
    const ACTION_EMAIL_DELETE = 'ACT_EM_DEL'; // Replaced by primary.
    const ACTION_EMAIL_VERIFY = 'ACT_EM_VER'; // Verified email via email link.

    // Fields that can be edited!

    private static $FIELDS_AVAIL = array("name_first", "name_last", "email",
        "rating", "prating", "email_action", "age",
        "location_state", "location_country", "experience",
        "suspended", "suspended_until", "created", "region", "divisioN");
    // General class variables
    private static $_ormAccount;
    private static $_action;
    private static $_actionDB;
    private static $_actionUser = 707070;
    
    /**
     * Write a note to the member's account, quick and easily!
     * 
     * @param string $action The action performed.
     * @param array $data The data to store.
     * @return boolean True on success, false otherwise,
     */
    private static function addNote($action, $data, $type=  Enum_Account_Note_Type::STANDARD){
        self::debugWrite("addNote: Added a note for ".$action.".", "cyan");
        ORM::factory("Account_Note")->writeNote(self::$_ormAccount, $action, self::$_actionUser, $data, $type);
    }
    
    /**
     * Write a debug message.
     */
    private static function debugWrite($text, $colour=null){
        if(!self::$_debug) return;
        if($colour != null){
            Minion_CLI::write("\t-".Minion_CLI::color($text, $colour));
        } else {
            Minion_CLI::write("\t-".Minion_CLI::color($text, "white"));
        }
    }

    /**
     * Write a replacement debug message.
     */
    private static function debugWriteReplace($text, $end_line=false){
        if(!self::$_debug) return;
        Minion_CLI::write_replace("\t-".$text, $end_line);
    }

    /**
     * Load the specified member into this class.
     * 
     * @param int $cid The CID to load.
     * @return boolean True when loaded, false otherwise.
     */
    public static function loadMember($cid) {
        if (self::$_ormAccount != null && self::$_ormAccount->loaded() && self::$_ormAccount->id = $cid) {
            return true;
        }
        self::$_ormAccount = ORM::factory("Account", $cid);
        self::debugWrite("processMember: Loaded the member account", "green");
        return self::$_ormAccount->loaded();
    }

    /**
     * Main method for processing a set of user details.
     * 
     * <pre>
     * Details can expect the following keys:
     * - cid
     * - name_first
     * - name_last
     * - rating
     * - prating
     * - email
     * - email_action = The action to carry out on the email.
     * - age
     * - location_state
     * - location_country
     * - experience
     * - suspended
     * - suspended_until
     * - created
     * - region
     * - division
     * - state (access/account state)
     * </pre>
     * 
     * @param type $details The array of details, keys as above.
     * @return boolean True on success, false otherwise.
     */
    public static function processMember($details = array(), $action = null) {
        // Check the action type
        if ($action != self::ACTION_CERT && $action != self::ACTION_USER) {
            self::debugWrite("processMember: action was not valid", "red");
            throw new Kohana_Exception("'action' must be a valid enum type.");
            return false;
        }
        self::$_action = $action;

        // If the member's CID hasn't been provided, error.
        if (Arr::get($details, "cid", 0) == 0) {
            self::debugWrite("processMember: CID not provided.", "red");
            return false;
        }
        $cid = Arr::get($details, "cid");

        // If the member's email address is one of the known VATSIM "deleted" ones
        if (in_array(Arr::get($details, "email"), array("noreply@vatsim.net", "no-reply@vatsim.net"))) {
            self::debugWrite("processMember: email is known list of banned.", "red");
            return false;
        }

        // Let's load the account and store it within
        self::$_ormAccount = null;
        self::loadMember($cid);

        // Try all the processing
        try {
            // Process general details
            self::debugWrite("processMember: processing general details....", "cyan");
            self::_processGeneralDetails($cid, $details);

            // Process email
            if (Arr::get($details, "email", null) != null && Arr::get($details, "email_action", null) != null) {
            self::debugWrite("processMember: processing email address with action...", "cyan");
                self::_processEmail($cid, Arr::get($details, "email"), Arr::get($details, "email_action"));
            } elseif (Arr::get($details, "email", null) != null) {
                self::debugWrite("processMember: processing email address with assumed action...", "cyan");
                self::_processEmail($cid, Arr::get($details, "email"), self::ACTION_EMAIL_PRIMARY_DEMOTE);
            }

            // Process state
            if ((Arr::get($details, "rating", null) != null && Arr::get($details, "region", null) != null &&
                    Arr::get($details, "region", null) != null) || (Arr::get($details, "state", null) != null)) {
                self::debugWrite("processMember: processing state with logical assumptions...", "cyan");
                self::_processStateLogic($cid, $details);
            }

            // Process status
            if (Arr::get($details, "rating", null) != null && Arr::get($details, "suspended_until", "0000-00-00") != "0000-00-00") {
                self::debugWrite("processMember: processing account status with logical assumptions....", "cyan");
                self::_processStatus($cid, $details);
            }

            // Process qualifications.
            if (Arr::get($details, "rating", null) != null || Arr::get($details, "prating", null) != null) {
                self::debugWrite("processMember: processing qualifications (ratings)....", "cyan");
                self::_processQualifications($cid, $details);
            }

            // Update the last time this user was checked
            self::$_ormAccount->checked = gmdate("Y-m-d H:i:s");
            self::$_ormAccount->save();
            self::debugWrite("processMember: updated the last checked date.", "green");
            return self::$_ormAccount->saved();
        } catch (Exception $ex) {
            // Log the message.
        }

        // Return the result of saving and nullifying this account.
        return (self::$_ormAccount->saved() && self::$_ormAccount = null);
    }

    /**
     * Process the general details of a member.
     * 
     * @param type $cid - The CID of the member.
     * @param type $details - The array of details.  See {@link processMember} for a list of keys.
     * @return boolean
     */
    private static function _processGeneralDetails($cid, $details = array()) {
        self::debugWrite("processGeneralDetails: started", "cyan");
        // CID? (ONLY if it's a new member!)
        $_actionDB = Helper_Membership_Account::ACTION_UPDATE;
        if (!self::$_ormAccount->loaded()) {
            $_actionDB = Helper_Membership_Account::ACTION_INSERT;
            self::$_ormAccount->id = $cid;
        }

        // Go through the various fields we can update.
        self::debugWrite("processGeneralDetails: updating fields....", "cyan");
        foreach (self::$_ormAccount->list_columns() as $_col => $_data) {
            if(strcasecmp(self::$_ormAccount->{$_col}, Arr::get($details, $_col)) != 0 && in_array($_col, self::$FIELDS_AVAIL)){
                self::debugWrite("processGeneralDetails: updating ".$_col." from ".self::$_ormAccount->{$_col}." to ".Arr::get($details, $_col).".");
                self::$_ormAccount->{$_col} = Arr::get($details, $_col, self::$_ormAccount->{$_col});
            }
        }

        // Now, save the details
        try {
            $changed = self::$_ormAccount->changed();
            self::$_ormAccount->save();
            self::debugWrite("processGeneralDetails: Saved account successfully.");
        } catch (Exception $ex) {
            return false; // Error is automatically logged.
        }

        // determine changed values
        foreach ($changed as $key => $value) {
            if (in_array($key, array("id", "created", "updated", "checked", "password", "token", "token_ip")))
                continue; // Keys to ignore.
                
            // add a separate note to member's account for each changed value
            if($key == "age"){
                self::addNote("ACCOUNT/DETAILS_CHANGED", array(
                    $key,
                    Enum_Account_Age::getDescription($value["old"]),
                    Enum_Account_Age::getDescription($value["new"]),
                ));
            } else {
                self::addNote("ACCOUNT/DETAILS_CHANGED", array($key, $value["old"], $value["new"]));
            }
            self::debugWrite("processGeneralDetails: writing note to detail the update of ".$key.".");
        }

        return self::$_ormAccount->saved();
    }

    /**
     * Process this email address for this member's account.
     * 
     * @param type $cid
     * @param type $email
     * @param type $primary
     */
    private static function _processEmail($cid, $email, $action = null) {
        self::debugWrite("processEmail: Started.", "cyan");
        switch ($action) {
            // Create a new email address
            case self::ACTION_EMAIL_CREATE:
            case self::ACTION_EMAIL_CREATE_PRIMARY:
                // Now, add the new primary email to this account!
                $_newPrimary = ORM::factory("Account_Email");
                $_newPrimary->account_id = self::$_ormAccount;
                $_newPrimary->email = $email;
                $_newPrimary->primary = ($action == self::ACTION_EMAIL_CREATE_PRIMARY) ? 1 : 0;
                $_newPrimary->created = gmdate("Y-m-d H:i:s");
                $_newPrimary->save();
                self::debugWrite("processEmail: added a new email.");

                // Log this change.
                self::addNote("EMAIL/ADDED", array($_newPrimary->id, $_newPrimary->email));

                // Primary log?
                if ($action == self::ACTION_EMAIL_CREATE_PRIMARY) {
                    self::debugWrite("processEmail: added email is now primary.");
                    self::addNote("EMAIL/PRIMARY_PROMOTED", array($_newPrimary->id, $_newPrimary->email));
                }
                break;

            // Set the new primary email.
            case self::ACTION_EMAIL_PRIMARY_DEMOTE:
            case self::ACTION_EMAIL_PRIMARY_DELETE:
                // First, get the current primary email.
                $_oldPrimary = self::$_ormAccount->emails->where("primary", "=", 1)->find();

                // If it's not changed, ignore it.
                if (strcasecmp($_oldPrimary->email, $email) == 0) {
                    self::debugWrite("processEmail: cannot update primary email, as it's the same.", "red");
                    return false;
                }

                // Demote or delete?
                if ($action == self::ACTION_EMAIL_PRIMARY_DEMOTE && $_oldPrimary->loaded()) {
                    $_oldPrimary->primary = 0;
                    $_oldPrimary->save();
                    self::debugWrite("processEmail: demoted old primary email.");

                    // Log this change.
                    self::addNote("EMAIL/PRIMARY_DEMOTED", array($_oldPrimary->id, $_oldPrimary->email));
                } else if ($action == self::ACTION_EMAIL_PRIMARY_DELETE && $_oldPrimary->loaded()) {
                    self::_processEmail($cid, $_oldPrimary->email, self::ACTION_EMAIL_DELETE);
                    self::debugWrite("processEmail: requested deletion of old primary email.");
                }

                // Add the new primary email address
                self::_processEmail($cid, $email, constant("self::ACTION_EMAIL_CREATE_PRIMARY"));

                // Now verify this email address!
                self::_processEmail($cid, $email, constant("self::ACTION_EMAIL_VERIFY"));

                return $_oldPrimary->saved();
                break;

            // Update an email to be "verified" once again.
            case self::ACTION_EMAIL_VERIFY:
                // Get this email & update the verified date.
                $_ormEmail = self::$_ormAccount->emails->where(DB::expr("LOWER(`email`)"), "=", strtolower($email))->where("deleted", "=", NULL)->find();

                if ($_ormEmail->loaded()) {
                    $_ormEmail->verified = gmdate("Y-m-d H:i:s");
                    $_ormEmail->save();
                    self::debugWrite("processEmail: requested deletion of old primary email.");
                } else {
                    return false;
                }

                // Log this change.
                // TODO: Different users.
                self::addNote("EMAIL/VERIFIED", array($_ormEmail->id, $_ormEmail->email));

                return $_ormEmail->saved();
                break;

            case self::ACTION_EMAIL_DELETE:
                // Get this email & update the deleted time.
                $_ormEmail = self::$_ormAccount->emails->where(DB::expr("LOWER(`email`)"), "=", strtolower($email))->where("deleted", "=", NULL)->find();

                if (!$_ormEmail->loaded()) {
                    return false;
                }

                $_ormEmail->deleted = gmdate("Y-m-d H:i:s");
                $_ormEmail->save();

                // Log this change.
                self::addNote("EMAIL/DELETED", array($_ormEmail->id, $_ormEmail->email));

                return $_ormEmail->saved();
                break;

            default:
                return false;
        }
    }

    /**
     * Process the member's state.
     * 
     * @param int $cid The CID of the user.
     * @param int $state The state to switch the user to.
     * @return boolean True if successfully changed, false otherwise.
     */
    private static function _processState($cid, $state) {
        // Get the member's current state
        $_ormAccountStateOld = self::$_ormAccount->states->where("removed", "=", NULL)->find();

        // If the new state matches the old state, ignore.
        if ($_ormAccountStateOld->loaded() && $_ormAccountStateOld->state == $state) {
            return false;
        }

        // Let's set the state to "dead"
        if ($_ormAccountStateOld->loaded()) {
            $_ormAccountStateOld->removed = gmdate("Y-m-d H:i:s");
            $_ormAccountStateOld->save();
        }

        // apply the new state
        $_ormAccountState = ORM::factory("Account_State");
        $_ormAccountState->account_id = self::$_ormAccount;
        $_ormAccountState->state = $state;
        $_ormAccountState->created = gmdate("Y-m-d H:i:s");
        $_ormAccountState->save();

        // Log this change.
        self::addNote("STATE/CHANGED", array(Enum_Account_State::idToType($_ormAccountStateOld->state),
                                             Enum_Account_State::idToType($_ormAccountState->state)));

        // Saved successfully?
        return (($_ormAccountStateOld->loaded() && $_ormAccountStateOld->saved()) || !$_ormAccountStateOld->loaded()) && $_ormAccountState->saved();
    }

    /**
     * Make some assumptions about a member's state and process it accordingly.
     * State = division member, tranferee etc
     * 
     * This function requires the rating, region and division.
     * 
     * @param int $cid The CID of the member.
     * @param array $details An array of details, for keys see {@link processMember}
     * @return boolean True if successfully changed state, false otherwise.
     */
    private static function _processStateLogic($cid, $details) {
        // Check for required details
        if ((Arr::get($details, "rating", null) == null || Arr::get($details, "region", null) == null || Arr::get($details, "division", null) == null) && Arr::get($details, "state", null) == null) {
            throw new Kohana_Exception("'details' must contain rating, region and division OR state.");
            return false;
        }

        // get their CURRENT state
        $_ormState = self::$_ormAccount->states->where("removed", "=", null)->find();
        if ($_ormState->loaded()) {
            $_state = Enum_Account_State::stringToID($_ormState->state);
        } else {
            $_state = Enum_Account_State::GUEST; // Everyone has to work their way up the ratings.
        }

        // EASY - is the state set?
        if (Arr::get($details, "state", null) != null) {
            if ($_state != Arr::get($details, "state")) {
                return self::_processState($cid, Arr::get($details, "state"));
            }
        }

        /**
         * ASSUMPTION NUMBER 1 - Region membership
         * 
         * IF:
         * + They are a region member (EUR)
         * + They are not a member of our division (GBR)
         * */
        if (strcasecmp(Arr::get($details, "region"), "eur") == 0 && strcasecmp(Arr::get($details, "division"), "gbr") != 0) {
            // If this member isn't marked as region member, mark it
            if ($_state != Enum_Account_State::REGION) {
                return self::_processState($cid, Enum_Account_State::REGION);
            }
            return false;
        }

        /**
         * ASSUMPTION NUMBER 2 - New member to VATSIM, i.e. NOT transferring
         * (will also catch OBS (rating 1) members that have transferred from any region/division)
         * 
         * IF:
         * + We don't currently have a state for them
         * + they have a rating less than or equal to 1
         * + they are in europe
         * + they are in our division
         * */
        if (Arr::get($details, "rating") <= 1 && !$_ormState->loaded() &&
                strcasecmp(Arr::get($details, "region"), "eur") == 0 && strcasecmp(Arr::get($details, "division"), "gbr") == 0) {
            // If this member isn't marked as a division member, let's do it.
            if ($_state != Enum_Account_State::DIVISION) {
                return self::_processState($cid, Enum_Account_State::DIVISION);
            }
            return false;
        }

        /**
         * ASSUMPTION NUMBER 3 - New member to division, transferring
         * 
         * IF:
         * + We don't currently have a state for them
         * + they have a rating of > 1
         * + they are in europe
         * + they are in our division
         * */
        if (Arr::get($details, "rating") > 1 && !$_ormState->loaded() &&
                strcasecmp(Arr::get($details, "region"), "eur") == 0 && strcasecmp(Arr::get($details, "division"), "gbr") == 0) {
            // If this member isn't marked as a transfer member, let's do it.
            if ($_state != Enum_Account_State::TRANSFER) {
                return self::_processState($cid, Enum_Account_State::TRANSFER);
            }
            return false;
        }
        
        // Since we've got here, we'll assume no update is necessary.
        return false;
    }

    /**
     * Process the status of the account.
     * Status = whether a member is active or disabled/suspended
     * 
     * This function requires the rating and suspended_until keys.
     * 
     * @param int $cid The CID of the member to update.
     * @param array $details The details of the member.  Full list of keys {@link processMember}
     * @param string $action The action to type - must be a valid enum type.
     */
    private static function _processStatus($cid, $details, $status = null, $statusPM = "+") {
        // Is status set in the details?
        if(Arr::get($details, "status", null) != null){
            $status = Arr::get($details, "status");
        }
        
        // Check for keys
        if ((Arr::get($details, "rating", null) == null || Arr::get($details, "suspended_until", "x") == "x") && $status == null) {
            throw new Kohana_Exception("'details' must contain rating and suspended_until");
        }

        // If the status has been set, just update it and return.
        if ($status != null) {
            // Get the current status.
            $_status = self::$_ormAccount->status;

            if ($statusPM == "=") {
                // We're going to SET this status.
                $_status = $status;
            } elseif ($statusPM == "+") {
                // We're going to ADD this status.
                $_status |= $status;
            } else {
                // We're going to REMOVE this status.
                $_status ^= $status;
            }

            // Update!
            self::$_ormAccount->status = $_status;
            self::$_ormAccount->save();

            // TODO: Log.
            switch($statusPM){
                case "=":
                    self::addNote("ACCOUNT/STATUS_SET", array($_status));
                    break;
                case "+":
                    self::addNote("ACCOUNT/STATUS_ADD", array(Enum_Account::idToType(decbin($status))));
                    break;
                default:
                    self::addNote("ACCOUNT/STATUS_DELETE", array(Enum_Account::idToType(decbin($status))));
            }
            return self::$_ormAccount->saved();
        }

        /**
         * Check number 1 - Member has become inactive.
         * 
         * IF:
         * + rating == -1
         * + status doesn't contain bitmask INACTIVE.
         */
        if (Arr::get($details, "rating") == -1 && !(self::$_ormAccount->status & bindec(Enum_Account::STATUS_INACTIVE))) {
            // TODO: Change status
            self::_processStatus($cid, $details, bindec(Enum_Account::STATUS_INACTIVE));

            // TODO: Log.
            self::addNote("ACCOUNT/STATUS_ASSUMPTION", array("INACTIVE"));
        }

        /**
         * Check number 2 - Member was previously inactive, now isn't
         * 
         * IF:
         * + rating != -1
         * + status contains bitmask INACTIVE.
         */
        if (Arr::get($details, "rating") != -1 && (self::$_ormAccount->status & bindec(Enum_Account::STATUS_INACTIVE))) {
            // TODO: Change status
            self::_processStatus($cid, $details, bindec(Enum_Account::STATUS_INACTIVE), "-");

            // TODO: Log.
            self::addNote("ACCOUNT/STATUS_ASSUMPTION", array("NOT INACTIVE"));
        }

        /**
         * Check number 3 - Member has become suspended.
         * 
         * IF:
         * + rating == 0
         * + status doesn't contain bitmask NETWORK_BANNED.
         */
        if (Arr::get($details, "rating") == 0 && !(self::$_ormAccount->status & bindec(Enum_Account::STATUS_NETWORK_BANNED))) {
            // TODO: Change status
            self::_processStatus($cid, $details, bindec(Enum_Account::STATUS_NETWORK_BANNED));

            // TODO: Log.
            self::addNote("ACCOUNT/STATUS_ASSUMPTION", array("NETWORK BANNED"));
        }

        /**
         * Check number 4 - Member was suspended, but is now active again.
         * 
         * IF:
         * + rating != 0
         * + status contains bitmask NETWORK_BANNED.
         */
        if (Arr::get($details, "rating") != 0 && (self::$_ormAccount->status & bindec(Enum_Account::STATUS_NETWORK_BANNED))) {
            // TODO: Change status
            self::_processStatus($cid, $details, bindec(Enum_Account::STATUS_NETWORK_BANNED), "-");

            // TODO: Log.
            self::addNote("ACCOUNT/STATUS_ASSUMPTION", array("NOT NETWORK BANNED"));
        }
    }

    /**
     * Process the endorsements for an account.
     * 
     * Requires the rating and prating fields to be set.
     * 
     * @param int $cid The ID of the member to update.
     * @param array $details the details of the member.  Full list of keys {@link processMember}
     * @return boolean True if added/changed, false otherwise.
     */
    private static function _processQualifications($cid, $details) {
        // If the ATC rating is above zero, we'll begin.
        if (Arr::get($details, "rating", 0) > 0) {
            if(Enum_Account_Qualification_ATC::idToType(Arr::get($details, "rating")) != Arr::get($details, "rating")):
                // Is there an older rating?
                $_atcROlder = self::$_ormAccount->qualifications
                                    ->where("type", "=", "ATC")
                                    ->where("removed", "IS", NULL)
                                    ->where("value", "!=", Arr::get($details, "rating"))
                                    ->order_by("value", "DESC")
                                    ->limit(1)
                                    ->find();
                if(!$_atcROlder->loaded()){
                    $_atcROlder = ORM::factory("Account_Qualification");
                    $_atcROlder->value = 0;
                }
                
                // Let's see if we currently have this rating.
                $_atcRCount = self::$_ormAccount->qualifications
                                    ->where("type", "=", "ATC")
                                    ->where("removed", "IS", NULL)
                                    ->where("value", "=", Arr::get($details, "rating"))
                                    ->count_all();
            
                // If it's less than 1, then they don't have this rating and it can be added.
                if($_atcRCount < 1){
                    $_ormATCQual = ORM::factory("Account_Qualification");
                    $_ormATCQual->account_id = self::$_ormAccount;
                    $_ormATCQual->type = "ATC";
                    $_ormATCQual->value = Arr::get($details, "rating", 0);
                    $_ormATCQual->created = gmdate("Y-m-d H:i:s");
                    $_ormATCQual->save();

                    // TODO: Log
                    if($_ormATCQual->saved()){
                        // LOG HERE!
                        self::addNote("QUALIFICATION/ATC_GRANTED", array(
                                Enum_Account_Qualification_ATC::getDescription($_ormATCQual->value),
                                Enum_Account_Qualification_ATC::idToType($_ormATCQual->value),
                                Enum_Account_Qualification_ATC::getDescription($_atcROlder->value),
                                Enum_Account_Qualification_ATC::idToType($_atcROlder->value),
                            ));
                    }
                }
            endif;
        }

        // If the pilot rating is above zero, and not an empty array, we'll begin!
        if (Arr::get($details, "prating", 0) > 0 || Arr::get($details, "prating", array()) != array()) {
            // Let's go through all ratings and add the ones that don't exist, as of "today".
            foreach(Arr::get($details, "prating") as $rating):
                if(Enum_Account_Qualification_Pilot::idToType($rating[1]) !== $rating[1]){
                    // Let's see if we currently have this rating.
                    $_pilRCount = self::$_ormAccount->qualifications
                                        ->where("type", "=", "PILOT")
                                        ->where("removed", "IS", NULL)
                                        ->where("value", "=", $rating[1])
                                        ->count_all();

                    // If it's less than 1, then they don't have this rating and it can be added.
                    if($_pilRCount < 1):
                        $_ormPilQual = ORM::factory("Account_Qualification");
                        $_ormPilQual->account_id = self::$_ormAccount;
                        $_ormPilQual->type = "PILOT";
                        $_ormPilQual->value = $rating[1];
                        $_ormPilQual->created = gmdate("Y-m-d H:i:s");
                        $_ormPilQual->save();

                        // TODO: Log
                        if($_ormPilQual->saved()){
                        // LOG HERE!
                        self::addNote("QUALIFICATION/PILOT_GRANTED", array(
                                Enum_Account_Qualification_Pilot::getDescription($_ormPilQual->value),
                                Enum_Account_Qualification_Pilot::idToType($_ormPilQual->value),
                            ));
                        }
                    endif;
                }
                
            endforeach;
        }
    }

}

?>