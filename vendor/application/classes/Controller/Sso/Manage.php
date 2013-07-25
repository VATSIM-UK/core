<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Account_Manage extends Controller_Account_Master {

    protected $_permissions = array(
        "_" => array('*'),
    );

    public function getDefaultAction() {
        return "dashboard";
    }

    public function before() {
        parent::before();
    }

    public function after() {
        parent::after();
    }

    public function action_dashboard() {
        die("DASHBOARD!");
    }

    public function action_email_confirm() {
        if (HTTP_Request::POST == $this->request->method()) {
            // Get the confirmation.
            $confirmed = $this->process_email_confirm();
            
            // If it's confirmed, store and redirect!
            if($confirmed){
                // Let's store this email!
                Helper_Membership_Account::processMember(array("cid" => $this->_account->id,
                                                               "email" => $this->request->post("email"),
                                                               "email_action" => Helper_Membership_Account::ACTION_EMAIL_CREATE_PRIMARY),
                                                         Helper_Membership_Account::ACTION_USER);
                
                // Redirect!
                $this->redirect("account/session/login_redirect");
                exit();
            }
            
            // It's not confirmed!!!! ERROR!!!!
            Session::instance()->set("errors", Session::instance()->get("errors", array()) + array("The email address you have supplied is not your VATSIM registered one, please try again."));
        }

        /** TEMPLATE SETTINGS * */
        $this->setTemplate("Manage/Email_Confirm");
    }

    protected function process_email_confirm() {
        // Firstly, are we logged in?
        if(!$this->_account->loaded()){
            $this->redirect();
            exit();
        }
        
        // Now, let's check the email they've provided against CERT.
        return Vatsim::factory("autotools")->confirm_email($this->_account->id, $this->request->post("email"));
    }

}