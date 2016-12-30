<?php

namespace App\Http\Controllers\Adm;

use Auth;
use View;
use App\Models\Mship\Account;

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

        $view->with('_account', $this->account);

        $this->buildBreadcrumb('Administration Control Panel', '/adm/dashboard');

        $view->with('_breadcrumb', $this->breadcrumb);

        $view->with('_pageTitle', $this->getTitle());
        $view->with('_pageSubTitle', $this->getSubTitle());

        return $view;
    }

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check()) {
                $this->account = Auth::user();
                $this->account->load('roles', 'roles.permissions');
            } else {
                $this->account = new Account();
            }

            return $next($request);
        });
    }
}
