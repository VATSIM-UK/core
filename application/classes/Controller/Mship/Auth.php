<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Mship_Auth extends Controller_Mship_Master {
    public function before(){
        parent::before();
        }
    
    /**
     * Allow the current user to login using their CID and password.
     */
    public function action_login(){
        // What's our return URL?
        if(!$this->request->query("return")){
            $returnURL = "/mship/manage/display";
            if($this->request->query("returnURL")){
                $returnURL = urldecode($this->request->query("returnURL"));
            }
            $_SESSION["return_url"] = $returnURL;
        }
        
        // Is this user already authenticated?
        if(false == true){
            $this->redirect($returnURL);
            return true;
        }
        
        // We're basically just going to go straight over VATSIM SSO.
        $SSO = Vatsim::factory("Sso");
        try {
            $details = $SSO->doRunSSO();
            $member = ORM::factory("Account", $details->user->id);
            $member->setSessionData();
        } catch(Exception $e){
            // TODO: Log.
            $this->setMessage("Authentication Error", "There was an error authenticating you, please try again.", "error");
            $this->redirect("/error/generic/VATSIM_SSO_AUTH");
            return false;
        }
        
        $this->redirect($_SESSION["return_url"]);
        return true;
    }
}