<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Membership_Account extends Controller_Membership_Master {
    protected $_permissions = array(
        "_" => array('*'),
    );
    
    public function getDefaultAction(){
        return "login";
    }

    public function before() {
        parent::before();

        // Add to the breadcrumb
        $this->addBreadcrumb("Account", "account");
    }

    public function after() {
        parent::after();
    }

    public function action_register() {
        if (HTTP_Request::POST == $this->request->method()) {
            // We're only looking for the type of registration here!
            $_regType = Enum_Account_Types::stringToID($this->request->post("account_type"));

            // Now we can store this account type to the temporary session data.
            Session::instance()->set("_regType", $_regType);

            // Now, we'll move to the details part of registering.
            $this->redirect("membership/account/register_details");
        } else {
            /** TEMPLATE SETTINGS * */
            $this->setTemplate("Account/Register");
            $this->addBreadcrumb("New Account", "register");
            $this->setTitle("Account Creation :: Stage 1");
        }
    }

    public function action_register_details() {
        // Get the current registration type.
        $_regType = Session::instance()->get("_regType");

        // Check the current "stage" of registration.
        if ($_regType == Enum_Account_Types::GUEST || Enum_Account_Types::idToType($_regType) == NULL) {
            $this->redirect("account/register");
            return;
        }

        // POST or GET?
        if (HTTP_Request::POST == $this->request->method()) {
            // Default values
            $account_id = NULL;
            $account_email_id = NULL;

            // Try and create their account
            try {
                // Password validation rule
                $pwVal = Validation::factory($_POST)
                        ->rule("password2", "matches", array(
                    ":validation", ":field", "password"
                ));


                // Main account
                $account = ORM::factory("Account");
                $account->name_first = $this->request->post("name_first");
                $account->name_last = $this->request->post("name_last");
                $account->password = $this->request->post("password");
                $account->gender = $this->request->post("gender");
                $dob = explode("/", $this->request->post("dob") . "/0/0/0");
                $account->dob = $dob[2] . "-" . $dob[1] . "-" . $dob[0];
                $account->created = date("Y-m-d H:i:s");
                $account->last_online = date("Y-m-d H:i:s");
                $account->create($pwVal);
                $account_id = $account->id;

                // Primary Email
                $accountEmail = ORM::factory("Account_Email");
                $accountEmail->id = strrev(uniqid());
                $accountEmail->account_id = $account_id;
                $accountEmail->email = $this->request->post("email");
                $accountEmail->primary = TRUE;
                $accountEmail->added = date("Y-m-d H:i:s");
                $accountEmail->create();
                $account_email_id = $accountEmail->id;

                // Since we've got to this point, it's a valid registration!
                // Where do we go from here?
                if ($_regType == Enum_Account_Types::BUSINESS) {
                    $nextDestination = "business/start";
                } elseif ($_regType == Enum_Account_Types::AGENT) {
                    $nextDestination = "agency/start";
                } elseif ($_regType == Enum_Account_Types::LANDLORD) {
                    $nextDestination = "account/dashboard";
                } else {
                    $nextDestination = "account/profile_edit";
                }

                // Cancel the registration.
                Session::instance()->get_once("_regType");

                // Force the login of a user!
                $this->process_login($account_id);

                // Send to the next destination
                $this->redirect($nextDestination);
            } catch (ORM_Validation_Exception $e) {
                // Since we've failed, delete the account and account email.
                if ($account_id) {
                    $account = ORM::factory("Account", $account_id);
                    $account->delete();
                }
                if ($account_email_id) {
                    $accountEmail = ORM::factory("Account_Email", $account_email_id);
                    $accountEmail->load()->delete();
                }

                // Set the errors for this template!
                $this->setErrors($e->errors("model"));
            }
        }

        /** TEMPLATE SETTINGS * */
        $this->setTemplate("Account/RegisterDetails");
        $this->addBreadcrumb("New Account Details", "membership/account/register_details");
        $this->setTitle("Account Creation :: Stage 2");
        $this->_data["regType"] = $_regType;
    }

    public function action_profile_edit() {
        
    }

    public function action_dashboard() {
        /** TEMPLATE SETTINGS * */
        $this->setTemplate("Account/Dashboard");
        $this->addBreadcrumb("Dashboard", "membership/account/dashboard");
        $this->setTitle("Account Dashboard");
        
    }

    public function action_login() {
        // POST or GET?
        if (HTTP_Request::POST == $this->request->method()) {
            // Find this email address.
            $accountEmail = ORM::factory("Account_Email")
                    ->where("email", "=", $this->request->post("email"))
                    ->where("verified", "IS NOT", NULL)
                    ->find();

            // Find the account.
            $account = $accountEmail->account;

            // Are they loaded?
            if (!$accountEmail->loaded() || !$account->loaded()) {
                $this->setErrors(array("login"));
            } else {
                // Check the password(s) match.  If they do, log them in!
                if (Valid::equals($account->password, sha1($this->request->post("password")))) {
                    // Process the login.
                    $this->process_login($account->id);

                    // Do they want remembering?
                    if ($this->request->post("remember")) {
                        // We need to create a cookie value and save it.
                        $account->hash = sha1($account->id . time());
                        $account->save();

                        // Now set the cookie.
                        Cookie::set("stu_savvy_auth", $account->hash);
                    }

                    // Now send to....
                    if ($this->request->param("extra")) {
                        $this->redirect(urldecode($this->request->param("extra")));
                    } else {
                        $this->redirect("membership/account/dashboard");
                    }
                    return;
                }
            }
        }

        /** TEMPLATE SETTINGS * */
        $this->setTemplate("Account/Login");
        $this->addBreadcrumb("Login", "membership/account/login");
        $this->setTitle("Login");
    }

    protected function process_login($account_id) {
        // Get this user account.
        $account = ORM::factory("Account", $account_id);

        // If it's not loaded, return home!
        if (!$account->loaded()) {
            $this->redirect("membership/account/login");
            return;
        }

        // Set the user session etc.
        Session::instance()->set($this->_data["config_session_name"], $account_id);
    }

    public function action_logout() {
        Session::instance()->destroy();
        $this->redirect("membership/account/login");
        return;
    }
    
    public function action_layout(){
          $this->setTemplate("Account/Test");
          $this->addBreadcrumb("Login", "membership/account/test");
          $this->setTitle("Test Layout");
    }
    
}