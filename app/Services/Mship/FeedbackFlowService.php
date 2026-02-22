<?php

namespace App\Services\Mship;

use App\Events\Mship\Feedback\NewFeedbackEvent;
use App\Models\Mship\Account;
use App\Models\Mship\Feedback\Answer;
use App\Models\Mship\Feedback\Form;
use App\Models\Mship\Feedback\Question;
use App\Services\Mship\DTO\FeedbackSubmitRedirectData;
use App\Services\Mship\DTO\FeedbackSubmitResult;
use App\Services\Mship\DTO\QuestionRenderContext;
use Illuminate\Support\Facades\Validator;

class FeedbackFlowService
{
    /**
     * @return array<string,string>
     */
    public function getPublicFeedbackFormsMap(): array
    {
        $forms = Form::whereEnabled(true)->orderBy('id', 'asc')->public()->getModels();
        $feedbackForms = [];

        foreach ($forms as $form) {
            $feedbackForms[$form->slug] = $form->name;
        }

        return $feedbackForms;
    }

    /**
     * @return Question[]
     */
    public function buildQuestionsForForm(Form $form, QuestionRenderContext $context): array
    {
        $questions = $form->questions()->orderBy('sequence')->get();

        foreach ($questions as $question) {
            $question->form_html = '';
            if ($question->type->requires_value == true) {
                if (isset($question->options['values'])) {
                    foreach ($question->options['values'] as $value) {
                        $selected = $context->old($question->slug) == $value ? 'checked' : '';
                        $question->form_html .= sprintf($question->type->code, $question->slug, $context->old($question->slug), $value, $value, $selected);
                    }
                }

                continue;
            }

            $defaultValues = ['usercid' => $context->cid];
            $question->form_html .= sprintf(
                $question->type->code,
                $question->slug,
                $context->old($question->slug, array_get($defaultValues, $question->slug))
            );
        }

        return $questions->all();
    }


    /**
     * @param  Question[]  $questions
     */
    public function canRenderForm(Form $form, array $questions): bool
    {
        return $form->enabled && count($questions) > 0;
    }


    public function buildSubmitRedirectData(FeedbackSubmitResult $result): FeedbackSubmitRedirectData
    {
        if ($result->isValidationFailed()) {
            return new FeedbackSubmitRedirectData(true, 'mship.feedback.new.form', errors: $result->errors, withInput: true);
        }

        if ($result->isSelfFeedbackError()) {
            return new FeedbackSubmitRedirectData(true, 'mship.feedback.new.form', message: (string) $result->message, withInput: true);
        }

        if ($result->isTargetResolutionFailed()) {
            return new FeedbackSubmitRedirectData(false, 'mship.manage.dashboard', message: (string) $result->message);
        }

        return new FeedbackSubmitRedirectData(false, 'mship.manage.dashboard', message: 'Your feedback has been recorded. Thank you!');
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function submitFeedback(Form $form, array $input, int $submitterId): FeedbackSubmitResult
    {
        $questions = $form->questions;
        $cidfield = null;
        $ruleset = [];
        $errormessages = [];
        $answerdata = [];

        foreach ($questions as $question) {
            $rules = [];

            if ($question->type->name == 'userlookup') {
                $cidfield = $question->slug;

                if (($input[$question->slug] ?? null) == $submitterId) {
                    return FeedbackSubmitResult::selfFeedbackError();
                }
            }

            if ($question->type->rules != null) {
                $rules = explode('|', $question->type->rules);
            }

            if ($question->required) {
                $rules[] = 'required';
            }

            if (count($rules) > 0) {
                $ruleset[$question->slug] = implode('|', $rules);
            }

            foreach ($rules as $rule) {
                $automaticRuleErrors = ['required', 'exists', 'integer'];
                if (! array_search($rule, $automaticRuleErrors)) {
                    $errormessages[$question->slug.'.'.$rule] = "Looks like you answered '".$question->question."' incorrectly. Please try again.";
                }
            }

            $errormessages[$question->slug.'.required'] = "You have not supplied an answer for '".$question->question."'.";
            $errormessages[$question->slug.'.exists'] = 'This user was not found. Please ensure that you have entered the CID correctly, and that they are a UK member';
            $errormessages[$question->slug.'.integer'] = 'You have not entered a valid integer.';

            $answerdata[] = new Answer([
                'question_id' => $question->id,
                'response' => $input[$question->slug] ?? null,
            ]);
        }

        $validator = Validator::make($input, $ruleset, $errormessages);
        if ($validator->fails()) {
            return FeedbackSubmitResult::validationFailed($validator->errors()->toArray());
        }

        $account = null;
        if (! $cidfield && ! $form->targeted) {
            $account = Account::find($submitterId);
        } elseif ($cidfield != null) {
            $account = Account::find($input[$cidfield] ?? null);
        } else {
            return FeedbackSubmitResult::targetResolutionFailed();
        }

        $feedback = $account->feedback()->create([
            'submitter_account_id' => $submitterId,
            'form_id' => $form->id,
        ]);

        $feedback->answers()->saveMany($answerdata);
        event(new NewFeedbackEvent($feedback));

        return FeedbackSubmitResult::success();
    }
}
