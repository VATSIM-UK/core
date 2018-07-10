<?php

namespace App\Http\Controllers\Adm\Mship\Feedback;

use App\Models\Mship\Feedback\Feedback as FeedbackModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class FeedbackSendController extends \App\Http\Controllers\BaseController
{
    public function store(FeedbackModel $feedback, Request $request)
    {
        $conditions = [];
        $conditions[] = $this->account->hasChildPermission('adm/mship/feedback/list') || $this->account->hasChildPermission('adm/mship/feedback/list/*');
        $conditions[] = $this->account->hasPermission('adm/mship/feedback/list/'.$feedback->form->slug);

        foreach ($conditions as $condition) {
            if ($condition && !$feedback->sent_at) {
                $feedback->markSent(\Auth::user(), $request->input('comment'));

                return Redirect::back()
                    ->withSuccess('Feedback sent to user!');
            }
        }

        abort(403, 'Unauthorized action.');
    }
}
