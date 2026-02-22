<?php

namespace App\Http\Controllers\Mship;

use App\Http\Requests\Mship\Feedback\SelectFeedbackFormRequest;
use App\Models\Mship\Feedback\Form;
use App\Services\Mship\DTO\QuestionRenderContext;
use App\Services\Mship\FeedbackFlowService;
use Illuminate\Http\Request;
use Redirect;

class Feedback extends \App\Http\Controllers\BaseController
{
    public function __construct(private FeedbackFlowService $feedbackFlowService)
    {
        parent::__construct();
    }

    public function getFeedbackFormSelect()
    {
        $this->setTitle('Submit Feedback');

        return $this->viewMake('mship.feedback.form')
            ->with('feedbackForms', $this->feedbackFlowService->getPublicFeedbackFormsMap());
    }

    public function postFeedbackFormSelect(SelectFeedbackFormRequest $request)
    {
        return Redirect::route('mship.feedback.new.form', [$request->input('feedback_type')]);
    }

    public function getFeedback(Form $form, Request $request)
    {
        $questions = $this->feedbackFlowService->buildQuestionsForForm(
            $form,
            new QuestionRenderContext((array) $request->session()->getOldInput(), $request->get('cid'))
        );

        if (! $this->feedbackFlowService->canRenderForm($form, $questions)) {
            return Redirect::route('mship.manage.dashboard')
                ->withError('There was an issue loading the requested form');
        }

        $this->setTitle($form->name ?? 'Submit Feedback');

        return $this->viewMake('mship.feedback.form')->with(['form' => $form, 'questions' => $questions]);
    }

    public function postFeedback(Form $form, Request $request)
    {
        $result = $this->feedbackFlowService->submitFeedback($form, $request->all(), (int) auth()->id());
        $redirectData = $this->feedbackFlowService->buildSubmitRedirectData($result);

        if ($redirectData->useBackRedirect) {
            $redirect = Redirect::back();

            if ($redirectData->errors !== []) {
                $redirect = $redirect->withErrors($redirectData->errors);
            }

            if ($redirectData->message !== null) {
                $redirect = $redirect->withError($redirectData->message);
            }

            if ($redirectData->withInput) {
                $redirect = $redirect->withInput();
            }

            return $redirect;
        }

        $redirect = Redirect::route($redirectData->route);

        if ($redirectData->message !== null) {
            if ($result->isSuccess()) {
                return $redirect->withSuccess($redirectData->message);
            }

            return $redirect->withError($redirectData->message);
        }

        return $redirect;
    }
}
