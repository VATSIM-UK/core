<?php

namespace App\Http\Controllers\Adm;

use App\Models\Mship\Feedback\Form;
use View;

class AdmController extends \App\Http\Controllers\BaseController
{
    /**
     * Setup the layout used by the controller.
     *
     * @return void
     */
    protected function setupLayout()
    {
        if (! is_null($this->layout)) {
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

        $view->with('_feedbackForms', Form::whereDeletedAt(null)->orderBy('id', 'asc')->getModels());

        $view->with('_account', $this->account);

        $this->buildBreadcrumb('Administration Control Panel', '/adm/dashboard');

        $view->with('_breadcrumb', $this->breadcrumb);

        $_account = $this->account;

        $view->with('_pageTitle', $this->getTitle());
        $view->with('_pageSubTitle', $this->getSubTitle());

        return $view;
    }
}
