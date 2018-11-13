<?php

namespace App\Http\Controllers\Adm\Mship;

use App\Http\Requests\Mship\Feedback\ExportFeedbackRequest;
use App\Http\Requests\Mship\Feedback\NewFeedbackFormRequest;
use App\Http\Requests\Mship\Feedback\UpdateFeedbackFormRequest;
use App\Models\Contact;
use App\Models\Mship\Feedback\Feedback as FeedbackModel;
use App\Models\Mship\Feedback\Form;
use App\Models\Mship\Feedback\Question;
use App\Models\Mship\Feedback\Question\Type;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class Feedback extends \App\Http\Controllers\Adm\AdmController
{
    public function getListForms()
    {
        $forms = Form::orderBy('id', 'asc')->get();
        $_account = $this->account;
        $forms = $forms->filter(function ($form, $key) use ($_account) {
            $hasWildcard = $_account->can('use-permission', 'adm/mship/feedback/list/*') || $_account->can('use-permission', 'adm/mship/feedback/configure/*');

            return $hasWildcard;
        })->all();

        return $this->viewMake('adm.mship.feedback.forms')
            ->with('forms', $forms);
    }

    public function getNewForm()
    {
        $question_types = Type::all();
        $new_question = new Question();

        return $this->viewMake('adm.mship.feedback.new')
            ->with('question_types', $question_types)
            ->with('new_question', $new_question);
    }

    public function postNewForm(NewFeedbackFormRequest $request)
    {
        $new_ident = $request->input('ident');
        $new_name = $request->input('name');
        $new_contact = $request->input('contact');
        $targeted = $request->input('targeted') == '1' ? true : false;
        $public = $request->input('public') == '1' ? true : false;
        if (Form::whereSlug($new_ident)->exists()) {
            return Redirect::back()
                ->withInput($request->input())
                ->withError('New form identifier \''.$new_ident.'\' already exists');
        }

        $form = $this->makeNewForm($new_ident, $new_name, $new_contact, $targeted, $public);

        return $this->configureForm($form, $request);
    }

    public function getConfigure(Form $form)
    {
        $question_types = Type::all();
        $current_questions = $form->questions()->orderBy('sequence')->notPermanent()->get();
        $new_question = new Question();

        return $this->viewMake('adm.mship.feedback.settings')
            ->with('question_types', $question_types)
            ->with('current_questions', $current_questions)
            ->with('new_question', $new_question)
            ->with('form', $form);
    }

    public function postConfigure(Form $form, UpdateFeedbackFormRequest $request)
    {
        return $this->configureForm($form, $request);
    }

    private function configureForm($form, $request)
    {
        $in_use_question_ids = [];

        $all_current_questions = $form->questions;
        $permanent_questions = $all_current_questions->filter(function ($question, $key) {
            if ($question->permanent) {
                return true;
            }

            return false;
        });
        foreach ($permanent_questions as $question) {
            $in_use_question_ids[] = ['id', '!=', $question->id];
        }

        $i = $permanent_questions->count() + 1;
        foreach (array_values($request->input('question')) as $question) {
            if (isset($question['exists'])) {
                // The question exisits already. Lets see if it is appropriate to create a new question, or update.
                $exisiting_question = Question::find($question['exists']);
                if ($exisiting_question->question != $question['name']) {
                    // Make a new question
                    $exisiting_question->delete();
                    $in_use_question_ids[] = ['id', '!=', $this->makeNewQuestion($form, $question, $i)];
                    $i++;
                    continue;
                }

                // We will update it instead
                $exisiting_question->required = $question['required'];
                $exisiting_question->slug = $question['slug'];
                $exisiting_question->sequence = $i;
                if (isset($question['options']['values'])) {
                    $question['options']['values'] = explode(',', $question['options']['values']);
                }
                if (isset($question['options'])) {
                    $exisiting_question->options = $question['options'];
                } else {
                    $exisiting_question->options = null;
                }

                $exisiting_question->required = $question['required'];
                $exisiting_question->save();
                $in_use_question_ids[] = ['id', '!=', $exisiting_question->id];
                $i++;
                continue;
            } else {
                // Make a new question
                $in_use_question_ids[] = ['id', '!=', $this->makeNewQuestion($form, $question, $i)];
                $i++;
                continue;
            }
        }

        //Check if we have lost any questions along the way, and delete them
        $form->questions()->where($in_use_question_ids)->delete();

        return Redirect::back()
            ->withSuccess('Updated!');
    }

    public function getEnableDisableForm(Form $form)
    {
        $form->enabled = !$form->enabled;
        $form->save();

        return Redirect::back()
            ->withSuccess('Updated!');
    }

    public function getFormVisibility(Form $form)
    {
        $form->public = !$form->public;
        $form->save();

        return Redirect::back()
            ->withSuccess('Updated!');
    }

    public function makeNewQuestion($form, $question, $sequence)
    {
        $type = Type::where('name', $question['type'])->first();
        $new_question = new Question();
        $new_question->question = $question['name'];
        $new_question->slug = $question['slug'].$sequence;
        $new_question->type_id = $type->id;
        $new_question->form_id = $form->id;
        if (isset($question['options']['values']) && $question['options']['values'] != '') {
            $question['options']['values'] = explode(',', $question['options']['values']);
        }
        if (isset($question['options'])) {
            $new_question->options = $question['options'];
        }
        $new_question->required = $question['required'];
        $new_question->sequence = $sequence;
        $new_question->permanent = false;
        $new_question->save();

        return $new_question->id;
    }

    public function makeUserCidQuestion($form)
    {
        $type = Type::where('name', 'userlookup')->first();
        $new_question = new Question();
        $new_question->question = 'CID of the member you are leaving feedback for.';
        $new_question->slug = 'usercid';
        $new_question->type_id = $type->id;
        $new_question->form_id = $form->id;
        $new_question->required = true;
        $new_question->sequence = 1;
        $new_question->permanent = true;
        $new_question->save();

        return $new_question->id;
    }

    public function makeNewForm($ident, $name, $contact, $targeted, $public)
    {
        $new_form = new Form();
        $new_form->slug = $ident;
        $new_form->name = $name;
        if ($contact != null && $contact != '') {
            $contact_model = Contact::whereEmail($contact);
            if ($contact_model->exists()) {
                $new_form->contact_id = $contact_model->first()->id;
            } else {
                $new_contact = new Contact();
                $contact_prefix = ucwords(preg_replace('/[^A-Za-z0-9]+/', ' ', explode('@', $contact)[0]));
                $contact_key = strtoupper(preg_replace('/[\s]+/', '_', $contact_prefix));
                $new_contact->key = $contact_key;
                $new_contact->name = $contact_prefix;
                $new_contact->email = $contact;
                $new_contact->save();
                $new_form->contact_id = $new_contact->id;
            }
        }
        $new_form->enabled = false;
        $new_form->targeted = $targeted;
        $new_form->public = $public;
        $new_form->save();

        if ($targeted) {
            $this->makeUserCidQuestion($new_form);
        }

        return $new_form;
    }

    public function getAllFeedback()
    {
        $feedback = FeedbackModel::with('account')->orderBy('created_at', 'desc')->paginate(15);

        return $this->viewMake('adm.mship.feedback.list')
            ->with('feedback', $feedback);
    }

    public function getFormFeedback($slug)
    {
        $form = Form::whereSlug($slug)->firstOrFail();
        $feedback = FeedbackModel::with('account')->orderBy('created_at', 'desc')->whereFormId($form->id)->paginate(15);

        return $this->viewMake('adm.mship.feedback.list')
            ->with('feedback', $feedback)
            ->with('form', $form);
    }

    public function getFormFeedbackExport($slug)
    {
        $form = Form::whereSlug($slug)->firstOrFail();

        return $this->viewMake('adm.mship.feedback.export')
            ->with('form', $form);
    }

    public function postFormFeedbackExport(ExportFeedbackRequest $request, $slug)
    {
        $form = Form::whereSlug($slug)->with('questions')->first();

        $from_date = new Carbon($request->input('from'));
        $to_date = new Carbon($request->input('to'));

        $query = $form->feedback()->with(['answers', 'account'])->whereBetween('created_at', [$from_date, $to_date]);

        if (!($request->input('include_actioned') && $request->input('include_unactioned'))) {
            if ($request->input('include_actioned')) {
                $query = $query->actioned();
            }
            if ($request->input('include_unactioned')) {
                $query = $query->unactioned();
            }
        }

        $query = $query->orderBy('created_at', 'desc')->get();

        \Excel::create($form->name.' Export '.Carbon::now()->format('d-m-Y Hi'), function ($excel) use ($form, $query, $request, $from_date, $to_date) {
            $excel->sheet('Sheet 1', function ($sheet) use ($form, $query, $request, $from_date, $to_date) {
                $sheet->rows(
                    [
                        ['Feedback Form:', $form->name],
                        ['From:', $from_date->format('d-m-Y')],
                        ['To:', $to_date->format('d-m-Y')],
                        ['Results:', $query->count()],
                        ['Generated at:', Carbon::now()->format('d-m-Y H:i')],
                        ['Generated by:', \Auth::user()->name],
                        ['All times ZULU'],
                        ['VATSIM UK'],
                        [''],
                        [''],
                    ]
                );

                // Headings
                $headings = [];
                if ($request->input('include_target') && $form->targeted) {
                    $headings[] = 'Targeted ID';
                    $headings[] = 'Targeted Name';
                }
                $headings[] = 'Question';
                $headings[] = 'Response';
                $headings[] = 'Submitted At';

                $sheet->appendRow($headings);

                // Append Feedback

                foreach ($query as $feedback) {
                    $prepend = [];

                    if ($request->input('include_target') && $form->targeted) {
                        $prepend[] = $feedback->account->id;
                        $prepend[] = $feedback->account->name;
                    }

                    $question_number = 1;

                    foreach ($feedback->answers as $response) {
                        if ($response->question->type->name == 'userlookup') {
                            continue;
                        }
                        if ($question_number == 1) {
                            $insert = $prepend;
                        } else {
                            $insert = [];
                            foreach ($prepend as $header) {
                                $insert[] = '';
                            }
                        }

                        $insert[] = $response->question->question;
                        $insert[] = $response->response;
                        if ($question_number == 1) {
                            $insert[] = $feedback->created_at->format('Y-m-d H:i');
                        }
                        $sheet->appendRow($insert);
                        $question_number++;
                    }
                    $sheet->appendRow(['']);
                }
            });
        })->download('xlsx');

        return Redirect::back();
    }

    public function getViewFeedback(FeedbackModel $feedback)
    {
        $targeted = $feedback->form->targeted;

        $this->authorize('use-permission', "adm/mship/feedback/view/{$feedback->formSlug()}");

        if ($this->account->id == $feedback->account_id && !$this->account->can('use-permission', 'adm/mship/feedback/view/own/')) {
            return redirect()->back()->withErrors('You may not view your own feedback.');
        }

        return $this->viewMake('adm.mship.feedback.view')
            ->with('feedback', $feedback)
            ->with('targeted', $targeted);
    }

    public function postActioned(FeedbackModel $feedback, Request $request)
    {
        $feedback->markActioned(\Auth::user(), $request->input('comment'));
        \Cache::forget($this->account->id.'.adm.mship.feedback.unactioned-count'); // Forget cached unactioned count

        return Redirect::back()
            ->withSuccess('Feedback marked as actioned!');
    }

    public function getUnActioned(FeedbackModel $feedback)
    {
        $feedback->markUnActioned();

        return Redirect::back()
            ->withSuccess('Feedback unmarked as actioned!');
    }
}
