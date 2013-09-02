<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Sso_Manage extends Controller_Sso_Master {
    /**
     * Display the user's details if they're logged in.
     * 
     * If a user is not logged in, send them to the SSO system to login.
     */
    public function action_display(){
        // If they're not logged in, we'll treat this as an SSO login.
        if(!$this->_current_account->loaded()){
            require_once "/var/www/sharedResources/SSO.class.php";
            $SSO = new SSO("CORE", URL::site("/sso/manage/display", "http"), false, "http://dev.vatsim-uk.co.uk/ALawrence/core.vatsim-uk.co.uk/sso/token/auth");
            $details = $SSO->member;
        }
        
        // Set the account details
        $this->_data["_account"] = $this->_current_account;
    }
    
    public function action_email_confirm(){
        // Submitted the form?
        if (HTTP_Request::POST == $this->request->method()) {
            // Is the email "valid"?
            $valid = false;
            try {
                if(Vatsim::factory("autotools")->confirm_email($this->_current_account->id, $this->request->post("email"))){
                    $valid = true;
                } else {
                    $this->_data["error"] = "This email address does not match your VATSIM registered one.  Please try again.";
                }
            } catch(Exception $e){
                $this->_data["error"] = "The VATSIM Certificate Server is currently offline and we are unable to validate your request.";
            }
            
            // Let's store it!
            if($valid){
                // Store and return to the site!
                try {
                    $this->_current_account->emails->set_primary($this->request->post("email"));
                } catch(Exception $e){
                    $valid = false;
                    print "<pre>"; print_r($e);
                    $this->_data["error"] = "There seems to be an error.  Please contact web services.";
                }
                
                // Send back to complete the checks!
                if($valid){
                    die("DONNNNNEEE!");
                    $this->redirect("/sso/auth/checks");
                    return;
                }
            }
            
            print $this->_data["error"]; exit();
        }
        
        $this->setTitle("Email Confirmation");
    }
    
    public function action_email_allocate(){
        
    }
    
}