<?php

namespace App\Http\Controllers\Adm\Mship;

use Illuminate\Http\Request;
use App\Models\Mship\Feedback\Feedback as FeedbackModel;
use App\Http\Controllers\Controller;

class Feedback extends \App\Http\Controllers\Adm\AdmController
{
    public function getAllFeedback()
    {
        if (!$this->account->hasChildPermission('adm/mship/feedback/list')) {
            abort(401, 'Unauthorized action.');
        }

        $feedback = FeedbackModel::with('account')->orderBy('created_at', 'desc')->get();

        return $this->viewMake('adm.mship.feedback.all')
                    ->with('feedback', $feedback);
    }

    public function getATCFeedback()
    {
        if (!$this->account->hasChildPermission("adm/mship/feedback/list/atc")) {
            abort(404, 'Unauthorized action.');
        }

        $feedback = FeedbackModel::with('account')->orderBy('created_at', 'desc')->atc()->get();

        return $this->viewMake('adm.mship.feedback.all')
                    ->with('feedback', $feedback);
    }

    public function getPilotFeedback()
    {
        if (!$this->account->hasPermission('adm/mship/feedback/list/pilot')) {
            abort(401, 'Unauthorized action.');
        }

        $feedback = FeedbackModel::with('account')->orderBy('created_at', 'desc')->pilot()->get();

        return $this->viewMake('adm.mship.feedback.all')
                    ->with('feedback', $feedback);
    }

    public function getViewFeedback(FeedbackModel $feedback)
    {
      if($this->account->hasChildPermission('adm/mship/feedback/list')){
        return $this->viewMake('adm.mship.feedback.view')
                  ->with('feedback', $feedback);
      }
      if ($this->account->hasChildPermission('adm/mship/feedback/list/atc') && $feedback->isATC() == true) {
          return $this->viewMake('adm.mship.feedback.view')
                    ->with('feedback', $feedback);
      }
      if ($this->account->hasChildPermission('adm/mship/feedback/list/pilot') && $feedback->isATC() == false) {
          return $this->viewMake('adm.mship.feedback.view')
                  ->with('feedback', $feedback);
      }
      abort(401, 'Unauthorized action.');


    }

}
