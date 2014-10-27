<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Sso_Email extends Model_Master {
    /**
     * Assign an email to an account.
     * 
     * @param int $account_email_id The account email ID
     * @param string $system The SSO system to assign the email to.
     * @return void
     */
    public function assign_email($account_email_id, $sso_account_id){
        // Is there an email for this already?
        if(ORM::factory("Sso_Email")->where("account_email_id", "=", $account_email_id)->where("sso_account_id", "=", $sso_account_id)->find()->loaded()){
            return;
        }
        
        // Assign the new email
        $newEmail = ORM::factory("Sso_Email");
        $newEmail->account_email_id = $account_email_id;
        $newEmail->sso_account_id = $sso_account_id;
        $newEmail->save();
    }
}

?>