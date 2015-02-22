<?php

namespace Controllers;

use \Auth;
use \View;
use \Session;
use \Request;
use \Models\Mship\Account;

class BaseController extends \Controller {

    protected $_account;
    protected $_pageTitle;

    public function __construct(){
        if(Auth::user()->check()){
            $this->_account = Auth::user()->get();
            $this->_account->load("roles", "roles.permissions");
        } else {
            $this->_account = new Account();
        }
    }

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
        $view->with("_account", $this->_account);

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
}
