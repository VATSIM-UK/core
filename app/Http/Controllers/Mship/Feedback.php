<?php

namespace App\Http\Controllers\Mship;

use App\Events\Mship\Feedback\NewFeedbackEvent;
use App\Models\Mship\Account;
use App\Models\Mship\Feedback\Answer;
use App\Models\Mship\Feedback\Form;
use App\Models\Mship\Feedback\Question;
use Illuminate\Http\Request;
use Redirect;
use Validator;

class Feedback extends \App\Http\Controllers\BaseController
{
    private $returnList;

    public function getFeedbackFormSelect()
    {
        $forms = Form::whereEnabled(true)->orderBy('id', 'asc')->public()->getModels();
        $feedbackForms = [];
        foreach ($forms as $f) {
            $feedbackForms[$f->slug] = $f->name;
        }

        $this->setTitle('Submit Feedback');

        return $this->viewMake('mship.feedback.form')
            ->with('feedbackForms', $feedbackForms);
    }

    public function postFeedbackFormSelect(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'feedback_type' => 'required|exists:mship_feedback_forms,slug',
        ]);
        if ($validator->fails()) {
            return Redirect::back()
                ->withErrors($validator)
                ->withInput();
        }

        return Redirect::route('mship.feedback.new.form', [$request->input('feedback_type')]);
    }

    public function getFeedback(Form $form)
    {
        /** @var Question[] $questions */
        $questions = $form->questions()->orderBy('sequence')->get();
        if (! $questions || ! $form->enabled) {
            // We have no questions to display!
            return Redirect::route('mship.manage.dashboard')
                ->withError('There was an issue loading the requested form');
        }

        // Lets parse the questions ready for inserting
        foreach ($questions as $question) {
            $question->form_html = '';
            if ($question->type->requires_value == true) {
                if (isset($question->options['values'])) {
                    foreach ($question->options['values'] as $key => $value) {
                        $selected = '';
                        if (old($question->slug) == $value) {
                            $selected = 'checked';
                        }
                        $question->form_html .= sprintf($question->type->code, $question->slug, old($question->slug), $value, $value, $selected);
                    }

                    continue;
                }

                // No values, so we cant use it :/
                continue;
            }

            $defaultValues = ['usercid' => request()->get('cid')];
            $question->form_html .= sprintf($question->type->code,
                $question->slug,
                old($question->slug, array_get($defaultValues, $question->slug))
            );
        }

        $this->setTitle($form->name ?? 'Submit Feedback');

        return $this->viewMake('mship.feedback.form')->with(['form' => $form, 'questions' => $questions]);
    }

    public function postFeedback(Form $form, Request $request)
    {
        $questions = $form->questions;
        $cidfield = null;
        // Make the validation rules
        $ruleset = [];
        $errormessages = [];
        $answerdata = [];

        foreach ($questions as $question) {
            $rules = [];

            if ($question->type->name == 'userlookup') {
                $cidfield = $question->slug;

                if ($request->input($question->slug) == \Auth::user()->id) {
                    return Redirect::back()
                        ->withError('You cannot leave feedback about yourself')
                        ->withInput();
                }
            }

            // Proccess rules

            if ($question->type->rules != null) {
                $rules = explode('|', $question->type->rules);
            }

            if ($question->required) {
                $rules[] = 'required';
            }
            if (count($rules) > 0) {
                $ruleset[$question->slug] = implode('|', $rules);
            }

            // Process errors
            foreach ($rules as $rule) {
                $automaticRuleErrors = ['required', 'exists', 'integer'];
                if (! array_search($rule, $automaticRuleErrors)) {
                    $errormessages[$question->slug.'.'.$rule] = "Looks like you answered '".$question->question."' incorrectly. Please try again.";
                }
            }
            $errormessages[$question->slug.'.required'] = "You have not supplied an answer for '".$question->question."'.";
            $errormessages[$question->slug.'.exists'] = 'This user was not found. Please ensure that you have entered the CID correctly, and that they are a UK member';
            $errormessages[$question->slug.'.integer'] = 'You have not entered a valid integer.';

            // Add the answer to the array, ready for inserting
            $answerdata[] = new Answer([
                'question_id' => $question->id,
                'response' => $request->input($question->slug),
            ]);
        }

        $validator = Validator::make($request->all(), $ruleset, $errormessages);
        if ($validator->fails()) {
            return back()->withErrors($validator)
                ->withInput();
        }

        $account = null;
        if (! $cidfield && ! $form->targeted) {
            // No specific target, feedback points at submitter
            $account = Account::find(\Auth::user()->id);
        } elseif ($cidfield != null) {
            $account = Account::find($request->input($cidfield));
        } else {
            // No one specified a user lookup field!
            return Redirect::route('mship.manage.dashboard')
                ->withError("Sorry, we can't process your feedback at the moment. Please check back later.");
        }

        // Make new feedback
        $feedback = $account->feedback()->create([
            'submitter_account_id' => \Auth::user()->id,
            'form_id' => $form->id,
        ]);

        // Add in the answers
        $feedback->answers()->saveMany($answerdata);
        event(new NewFeedbackEvent($feedback));

        return Redirect::route('mship.manage.dashboard')
            ->withSuccess('Your feedback has been recorded. Thank you!');
    }
}
