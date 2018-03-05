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

        $view->with('_feedbackForms', Form::whereDeletedAt(null)->orderBy('id', 'asc')->getModels());

        $view->with('_account', $this->account);

        $this->buildBreadcrumb('Administration Control Panel', '/adm/dashboard');

        $view->with('_breadcrumb', $this->breadcrumb);

        $_account = $this->account;
        $forms_with_unactioned = Cache::remember($_account->id.'.adm.mship.feedback.unactioned-count', 2, function () use($_account) {
            $forms = Form::orderBy('id', 'asc')->get(['id']);
            return $forms->transform(function ($form, $key) use ($_account) {
                $hasWildcard = $_account->hasPermission("adm/mship/feedback/list/*") || $_account->hasPermission("adm/mship/feedback/configure/*");
                $hasSpecific = $_account->hasPermission("adm/mship/feedback/list/".$form->slug) || $_account->hasPermission("adm/mship/feedback/configure/".$form->slug);

                if($hasWildcard || $hasSpecific){

                  return $form->feedback()->unActioned()->count();
                }
                return 0;
            })->sum();
        });

        if($forms_with_unactioned > 0){
          $view->with('_unactioned_feedback', $forms_with_unactioned);
        }

        $view->with('_pageTitle', $this->getTitle());
        $view->with('_pageSubTitle', $this->getSubTitle());

        return $view;
    }
}
