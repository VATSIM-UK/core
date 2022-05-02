<?php

namespace App\Http\Controllers\Adm;

use App\Models\Mship\Feedback\Form;
use Cache;
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
        $forms_with_unactioned = Cache::remember($_account->id.'.adm.mship.feedback.unactioned-count', 2 * 60, function () use ($_account) {
            $forms = Form::orderBy('id', 'asc')->get(['id']);

            return $forms->transform(function ($form, $key) use ($_account) {
                $hasPermission = $_account->can('use-permission', 'adm/mship/feedback/list/*') || $_account->can('use-permission', 'adm/mship/feedback/configure/*');

                if ($hasPermission) {
                    return $form->feedback()->unActioned()->count();
                }

                return 0;
            })->sum();
        });

        if ($forms_with_unactioned > 0) {
            $view->with('_unactioned_feedback', $forms_with_unactioned);
        }

        $view->with('_pageTitle', $this->getTitle());
        $view->with('_pageSubTitle', $this->getSubTitle());

        return $view;
    }
}
