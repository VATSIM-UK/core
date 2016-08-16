<?php

namespace App\Http\Controllers\Adm;

use Auth;
use Session;
use Route;
use View;
use App\Models\Mship\Account;
use Request;

class AdmController extends \App\Http\Controllers\BaseController
{

    /**
     * Setup the layout used by the controller.
     *
     * @return void
     */
    protected function setupLayout()
    {
        if (!is_null($this->layout)) {
            $this->layout = View::make($this->layout);
        }
    }

    public function __controller()
    {
        parent::__controller();
    }

    public function viewMake($view)
    {
        $view = View::make($view);

        $view->with("_account", $this->_account);

        $this->buildBreadcrumb("Administration", "/adm");

        $view->with("_breadcrumb", $this->_breadcrumb);

        $view->with("_pageTitle", $this->getTitle());

        return $view;
    }

    public function __construct()
    {
        if (Auth::check()) {
            $this->_account = Auth::user();
            $this->_account->load("roles", "roles.permissions");
        } else {
            $this->_account = new Account();
        }
    }

}
