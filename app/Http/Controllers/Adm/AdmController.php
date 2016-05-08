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

    protected $_pageSubTitle = null;

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

        // Accounts!
        $view->with("_account", $this->_account);

        // Let's also display the breadcrumb
        $breadcrumb = [];
        $uri = "/adm";
//            $bcBase = explode("\\", str_replace("App\\Http\\Controllers\\Adm\\", "", get_called_class()));
        $bcBase = explode("\\", get_called_class());
        $isModule = ($bcBase[1] == "Modules");

        if ($isModule) {
            $module = \Module::where("slug", strtolower($bcBase[2]))->first();

            $bcBaseStart = array_search("Controllers", $bcBase) + 2;
            $bcBase = array_slice($bcBase, $bcBaseStart);
            $bcBase = array_merge([$module["name"]], $bcBase);
        } else {
            $bcBaseStart = array_search("Controllers", $bcBase) + 2;
            $bcBase = array_slice($bcBase, $bcBaseStart);
        }

        /*for($i=2; $i<=10; $i++){
            if(Request::segment($i) != NULL){
                $uri.= Request::segment($i);
                $b = [Request::segment($i)];
                $b[1] = rtrim($uri, "/");
                $breadcrumb[] = $b;
            }
        }*/
        foreach ($bcBase as $bc) {
            $uri .= "/" . strtolower($bc);
            $breadcrumb[] = [strtolower($bc), $uri, Route::has($uri)];
        }
        $view->with("_breadcrumb", $breadcrumb);

        // Page titles
        if ($this->_pageTitle == null) {
            $this->setTitle(last($breadcrumb)[0]);
        }
        $view->with("_pageTitle", ucfirst($this->_pageTitle));
        if ($this->_pageSubTitle == null) {
            $this->_pageSubTitle = head($breadcrumb)[0];
        }
        if ($this->_pageSubTitle == $this->_pageTitle) {
            $this->_pageSubTitle = null;
        }
        $view->with("_pageSubTitle", $this->_pageSubTitle);

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
