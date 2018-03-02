<?php

namespace App\Http\Controllers\Adm;

use View;
use Cache;
use App\Models\Mship\Feedback\Form;

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

        $_account = $this->account;
        $forms_with_unactioned = Cache::remember('adm.mship.unactioned-forms', 2, function () use($_account) {
            $forms = Form::orderBy('id', 'asc')->get(['id']);
            return $forms->filter(function ($form, $key) use ($_account) {
                $hasWildcard = $_account->hasPermission("adm/mship/feedback/list/*") || $_account->hasPermission("adm/mship/feedback/configure/*");
                $hasSpecific = $_account->hasPermission("adm/mship/feedback/list/".$form->slug) || $_account->hasPermission("adm/mship/feedback/configure/".$form->slug);
                return ($hasWildcard || $hasSpecific) && $form->feedback()->unActioned()->count() > 0;
            })->count();
        });

        if($forms_with_unactioned > 0){
          $view->with('_unactioned_feedback', true);
        }

        $view->with('_pageTitle', $this->getTitle());
        $view->with('_pageSubTitle', $this->getSubTitle());

        return $view;
    }
}
