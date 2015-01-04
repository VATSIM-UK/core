<?php

namespace Controllers;

use \View;
use \Session;
use \Request;
use \Models\Mship\Account\Account;

class BaseController extends \Controller {

    protected $_current_account;
    protected $_real_account;
    protected $_pageTitle;

    /**
     * Setup the layout used by the controller.
     *
     * @return void
     */
    protected function setupLayout() {
        if (!is_null($this->layout)) {
            $this->layout = View::make($this->layout);
        }
    }

    protected function viewMake($view) {
        $view = View::make($view);

        // Accounts!
        $view->with("_account", $this->_current_account);
        if (Session::get("auth_override", 0) != 0) {
            $view->with("_account_override", true);
        } else {
            $view->with("_account_override", false);
        }

        // Let's also display the breadcrumb
        $breadcrumb = array();
        $uri = "/adm/";
        for($i=1; $i<=10; $i++){
            if(Request::segment($i) != NULL){
                $uri.= Request::segment($i);
                $b = [Request::segment($i)];
                $b[1] = rtrim($uri, "/");
                $breadcrumb[] = $b;
            }
        }
        $view->with("_breadcrumb", $breadcrumb);

        // Page titles
        if($this->_pageTitle == NULL){
            $this->_pageTitle = last($breadcrumb)[0];
        }
        $view->with("_pageTitle", ucfirst($this->_pageTitle));

        return $view;
    }

    public function __construct() {
        if (Session::get("auth_override", 0) != 0) {
            $this->_current_account = Account::find(Session::get("auth_override", 0));
            $this->_real_account = Account::find(Session::get("auth_account", 0));
        } else {
            $this->_current_account = Account::find(Session::get("auth_account", 0));
            $this->_real_account = Account::find(Session::get("auth_account", 0));
        }
    }

}
