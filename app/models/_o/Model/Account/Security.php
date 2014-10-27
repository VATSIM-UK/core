<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Account_Security extends Model_Master {
    // Validation rules
    public function rules() {
        return array(
            'value' => array(
                array("not_empty"),
                array(array($this, "validatePassword")),
            ),
        );
    }

    // Validate the passwords
    public function validatePassword($password) {
        // Create the name of the enum class
        $enum = "Enum_Account_Security_" . ucfirst(strtolower(Enum_Account_Security::valueToType($this->type)));

        // Does it meet the minimum length?
        if ($enum::MIN_LENGTH > 0) {
            if (strlen($password) < $enum::MIN_LENGTH) {
                return false;
            }
        }

        // Minimal alphabetic characters?
        if ($enum::MIN_ALPHA > 0) {
            preg_match_all("/[a-zA-Z]/", $password, $matches);
            $matches = isset($matches[0]) ? $matches[0] : $matches;
            if (count($matches) < $enum::MIN_ALPHA) {
                return false;
            }
        }

        // Minimal numeric characters?
        if ($enum::MIN_NUMERIC > 0) {
            preg_match_all("/[0-9]/", $password, $matches);
            $matches = isset($matches[0]) ? $matches[0] : $matches;
            if (count($matches) < $enum::MIN_NUMERIC) {
                return false;
            }
        }

        // Minimal non-alphanumeric
        if ($enum::MIN_NON_ALPHANUM > 0) {
            preg_match_all("/[^a-zA-Z0-9]/", $password, $matches);
            $matches = isset($matches[0]) ? $matches[0] : $matches;
            if (count($matches) < $enum::MIN_NON_ALPHANUM) {
                return false;
            }
        }

        $this->value = $this->hash($password);
        return true;
    }

    /**
     * Do we need to validate the user's second password, are we OK for a bit?
     * 
     * @return boolean TRUE if validation require, FALSE otherwise.
     */
    public function require_authorisation() {
        $gracePeriod = ORM::factory("Setting")->getValue("auth.sso.security.grace");
        $graceCutoff = gmdate("Y-m-d H:i:s", strtotime("-" . $gracePeriod));
        $lastSecurity = $this->session()->get("sso_security_grace", $graceCutoff);
        return (strtotime($lastSecurity . " GMT") <= strtotime($graceCutoff . " GMT"));
    }

    /**
     * Set the security on an account.
     * 
     * @param int $account_id The account to set security on.
     * @param int $type The security type.
     * @param string $password The password to set.
     */
    public function set_security($account_id, $type, $password) {
        // Delete old one, if exists
        $oldSecurity = ORM::factory("Account_Security")->where("account_id", "=", $account_id)->find();
        if ($oldSecurity->loaded()) {
            $oldSecurity->delete();
        }

        // Store new one!
        $newSecurity = ORM::factory("Account_Security");
        $newSecurity->account_id = $account_id;
        $newSecurity->type = $type;
        $newSecurity->value = $password;
        $newSecurity->save();
    }

    /**
     * Authorise a user's second security details.
     * 
     * @param string $security The second security layer password.
     * @param boolean $forceSession if set to true the grace time is updated regardless.
     * @return boolean True on success, false otherwise.
     */
    public function action_authorise($security = null, $forceSession = false) {
        // If this isn't loaded, they don't need a second password.
        if (!$this->loaded()) {
            return true;
        }

        // Let's validate!
        if ($this->hash($security) == $this->value) {
            ORM::factory("Account_Note")->writeNote($this->account, "ACCOUNT/AUTH_SECONDARY_SUCCESS", $this->id, array(), Enum_Account_Note_Type::AUTO);
            if ($this->require_authorisation() || $forceSession) {
                $this->session()->set("sso_security_grace", gmdate("Y-m-d H:i:s"));
            }
            return true;
        }
        ORM::factory("Account_Note")->writeNote($this->account, "ACCOUNT/AUTH_SECONDARY_FAILURE", $this->id, array(), Enum_Account_Note_Type::AUTO);
        // Default response for protection!
        return false;
    }

    /**
     * Kill the grace session!
     */
    public function action_deauthorise() {
        $this->session()->delete("sso_security_grace");
    }

    /**
     * Generate a new password reset code for the specified individual.
     * 
     * Will also queue an email for them.
     * 
     * @param int $account_id The account ID to generate a password reset link for.
     * @return boolean True on success, false otherwise.
     */
    public function action_generate_security_reset_token($account_id) {
        $account = ORM::factory("Account_Main", $account_id);
        if (!$account->loaded()) {
            return;
        }

        $token = ORM::factory("Sys_Token")->action_generate($account_id, Enum_System_Token::SECURITY_RESET, ORM::factory("Setting")->getValue("auth.sso.security.reset_time"));

        if (!$token->loaded()) {
            //TODO: Log this.
            return false;
        }

        ORM::factory("Account_Note")->writeNote($account, "ACCOUNT/SECURITY_RESET_REQUEST", $account->id, array($token->code), Enum_Account_Note_Type::AUTO);
        
        // Queue an email!
        ORM::factory("Postmaster_Queue")->action_add("SSO_SLS_RESET", $account_id, null, array(
            "timestamp" => $token->created,
            "ip_address" => Arr::get($_SERVER, "REMOTE_ADDR", "Unavailable"),
            "reset_code" => $token->code,
        ));

        return true;
    }

    /**
     * Generate a new secondary password.
     * 
     * @param int $account_id The account ID to generate a new password for.
     * @return boolean True on success, false otherwise.
     */
    public function action_generate_security_password($account_id) {
        // Let's generate....
        $random = str_shuffle(Text::random("alnum", 12) . Text::random("numeric", 4)) . "!";

        // Let's update....
        $resetAccount = ORM::factory("Account", $account_id);
        $resetAccount->security->value = $random;
        $resetAccount->security->created = gmdate("Y-m-d H:i:s");
        $resetAccount->security->expires = gmdate("Y-m-d H:i:s");
        $resetAccount->security->save();

        ORM::factory("Account_Note")->writeNote($resetAccount, "ACCOUNT/SECURITY_RESET_CONFIRMED", $resetAccount->id, array(), Enum_Account_Note_Type::AUTO);
        
        // Now email them!
        ORM::factory("Postmaster_Queue")->action_add("SSO_SLS_FORGOT", $resetAccount->id, null, array("temp_password" => $random));
        return true;
    }

    // Save the new password
    public function save(Validation $validation = NULL) {// Let's just update the expiry!
        $enum = "Enum_Account_Security_" . ucfirst(strtolower(Enum_Account_Security::valueToType($this->type)));
        if ($this->expires == null) {
            $this->created = gmdate("Y-m-d H:i:s");
            $this->expires = ($enum::MIN_LIFE > 0) ? gmdate("Y-m-d H:i:s", strtotime("+" . $enum::MIN_LIFE . " days")) : NULL;
            ORM::factory("Account_Note")->writeNote($this->account, "ACCOUNT/AUTH_SECONDARY_CREATED", $this->id, array(), Enum_Account_Note_Type::AUTO);
        }
        parent::save($validation);
    }
    
    public function delete(){
        // Now log.
        ORM::factory("Account_Note")->writeNote($this->account, "ACCOUNT/AUTH_SECONDARY_DELETED", $this->id, array(), Enum_Account_Note_Type::AUTO);
        
        parent::delete();
    }

}

?>