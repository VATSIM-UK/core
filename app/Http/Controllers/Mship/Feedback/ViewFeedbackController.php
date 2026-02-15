<?php

namespace App\Http\Controllers\Mship\Feedback;

use Redirect;

class ViewFeedbackController extends \App\Http\Controllers\BaseController
{
    public function show()
    {
        $feedback = $this->account->feedback()
            ->sent()
            ->get()
            ->reverse();

        if ($feedback->isEmpty()) {
            return Redirect::route('mship.manage.dashboard')->withError('You have no feedback available to view at this time.');
        }

        $this->setTitle('Your Feedback');

        return $this->viewMake('mship.feedback.view')
            ->with('feedback', $feedback);
    }
}
