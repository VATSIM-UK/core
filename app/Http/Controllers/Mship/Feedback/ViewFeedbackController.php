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
        $displayData = $this->feedbackViewService->getDisplayData($this->account);

        if (! $displayData->canDisplay) {
            return Redirect::route('mship.manage.dashboard')->withError((string) $displayData->errorMessage);
        }

        $this->setTitle('Your Feedback');

        return $this->viewMake('mship.feedback.view')
            ->with('feedback', $displayData->feedback);
    }
}
