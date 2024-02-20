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

    public function getFeedback(Form $form, Request $request)
    {
        // Get a list of "pre-fillable" questions from the request, based on question slug
        // To allow direct links from e.g euroscope profiles via vatsim.uk/atcfb?cid=12345
        $preFillable = $request->only(['cid']);
        $preFilled = ['usercid' => array_get($preFillable, 'cid')];

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

            $fromQuery = array_get($preFilled, $question->slug);
            $question->form_html .= sprintf($question->type->code, $question->slug, old($question->slug, $fromQuery));
        }

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

    public function getUserSearch($name, Request $request)
    {
        $matches = Account::whereRaw("CONCAT(`name_first`, ' ',`name_last`) LIKE \"%".$name.'%"')
            ->where('id', '!=', \Auth::user()->id)
            ->limit(5)
            ->with(['states'])
            ->get(['id', 'name_first', 'name_last']);

        $this->returnList = collect();

        $matches->transform(function ($user, $key) {
            foreach ($user->states as $key => $state) {
                if ($state->is_permanent) {
                    if ($state->code = 'INTERNATIONAL' && ($state->region->first() == '*' || $state->division->first() == '*')) {
                        $user->state = 'Intl.';
                    } else {
                        $user->state = $state->region->first().'/'.$state->division->first();
                    }
                }
            }

            $this->returnList->push(collect([
                'cid' => $user->id,
                'name' => e($user->real_name),
                'status' => $user->state,
            ]));

            return $user;
        });
        $matches = null;

        return response()->json($this->returnList);
    }

    public function redirectNewAtc(Request $request)
    {
        return redirect()->route('mship.feedback.new.form', array_merge(['atc'], $request->query->all()));
    }
}
