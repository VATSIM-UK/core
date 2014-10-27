<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Account_Email extends Model_Master {
    /**
     * Check whether the current email has been assigned to an SSO system.
     * 
     * @param string $sso_account_id The system to check for an assignment for.
     * @param integer $id The account ID to use.
     * @param boolean $returnEmail If set to TRUE the email will be returned, instead of true/false.
     * @return boolean TRUE if this system has an email assignment. FALSE otherwise.
     */
    public function assigned_to_sso($sso_account_id, $id = null, $returnEmail = false) {
        // Use the ID?
        if ($id != null) {
            $this->where("account_id", "=", $id);
        }

        // Let's loop through ALL emails
        foreach ($this->where("deleted", "IS", NULL)->find_all() as $email) {
            $sso = $email->sso_email->find();
            if ($sso->loaded() && $sso->sso_account_id == $sso_account_id) {
                if ($returnEmail && $sso->email->loaded()) {
                    return $sso->email->email;
                } else {
                    return true;
                }
            }
        }

        // If we still don't have a return by now, let's just get the primary!
        // Use the ID?
        if ($id != null) {
            $this->where("account_id", "=", $id);
        }
        return $this->get_active_primary(false)->email;
    }
}

?>
