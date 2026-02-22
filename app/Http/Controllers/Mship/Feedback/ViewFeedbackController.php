<?php

namespace App\Http\Controllers\Mship\Feedback;

use App\Services\Mship\FeedbackViewService;
use Redirect;

class ViewFeedbackController extends \App\Http\Controllers\BaseController
{
    public function __construct(private FeedbackViewService $feedbackViewService)
    {
        parent::__construct();
    }

    public function show()
    {
        $feedback = $this->feedbackViewService->getSentFeedback($this->account);

        if ($feedback->isEmpty()) {
            return Redirect::route('mship.manage.dashboard')->withError('You have no feedback available to view at this time.');
        }

        $this->setTitle('Your Feedback');

        return $this->viewMake('mship.feedback.view')
            ->with('feedback', $feedback);
    }
}
