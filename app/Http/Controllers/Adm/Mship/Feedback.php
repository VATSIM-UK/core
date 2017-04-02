<?php

namespace App\Http\Controllers\Adm\Mship;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Mship\Feedback\Question;
use Illuminate\Support\Facades\Redirect;
use App\Models\Mship\Feedback\Question\Type;
use App\Models\Mship\Feedback\Feedback as FeedbackModel;
use App\Http\Requests\Mship\Feedback\UpdateFeedbackFormRequest;

class Feedback extends \App\Http\Controllers\Adm\AdmController
{
    public function getConfigure()
    {
        $question_types = Type::all();
        $current_questions = Question::orderBy("sequence")->notPermanent()->get();
        $new_question = new Question();

        foreach ($question_types as $key => $type) {
          if(!$type->canBeUsedAgain()){
            $question_types->pull($key);
          }
        }

        return $this->viewMake('adm.mship.feedback.settings')
                    ->with('question_types', $question_types)
                    ->with('current_questions', $current_questions)
                    ->with('new_question', $new_question);
    }

    public function postConfigure(UpdateFeedbackFormRequest $request)
    {
        $in_use_question_ids = [];


        $all_current_questions = Question::all();
        $permanent_questions = $all_current_questions->filter(function ($question, $key) {
            if($question->permanent){
              return true;
            }
            return false;
        });
        foreach ($permanent_questions as $question) {
          $in_use_question_ids[] = ['id', '!=', $question->id];
        }

        $sequence_start_number = $permanent_questions->count() + 1;
        $i = $sequence_start_number;


        foreach($request->input('question') as $question){
          if(isset($question['exists'])){
            // The question exisits already. Lets see if it is appropriate to create a new question, or update.
            $exisiting_question = Question::find($question['exists']);
            if($exisiting_question->question != $question['name']){
                // Make a new question
                $exisiting_question->delete();
                $in_use_question_ids[] = ['id', '!=', $this->makeNewQuestion($question, $i)];
                $i++;
                continue;
            }

            // We will update it instead
            $exisiting_question->required = $question['required'];
            $exisiting_question->slug = $question['slug'] . $i;
            $exisiting_question->sequence = $i;
            if(isset($question['options']['values'])){
              $question['options']['values'] = explode(",", $question['options']['values']);
            }
            if(isset($question['options'])){
              $exisiting_question->options = $question['options'];
            }else{
              $exisiting_question->options = array();
            }

            $exisiting_question->required = $question['required'];
            $exisiting_question->save();
            $in_use_question_ids[] = ['id', '!=', $exisiting_question->id];
            $i++;
            continue;
          }else{
            // Make a new question
            $in_use_question_ids[] = ['id', '!=', $this->makeNewQuestion($question, $i)];
            $i++;
            continue;
          }
        }

        //Check if we have lost any questions along the way, and delete them
        Question::where($in_use_question_ids)->delete();

        return Redirect::back()
                      ->withSuccess("Updated!");
    }

    function makeNewQuestion($question, $sequence){
      $type = Type::where('name', $question['type'])->first();
      $new_question = new Question();
      $new_question->question = $question['name'];
      $new_question->slug = $question['slug'] . $sequence;
      $new_question->type_id  = $type->id;
      if(isset($question['options']['values']) && $question['options']['values'] != ""){
        $question['options']['values'] = explode(",", $question['options']['values']);
      }
      if(isset($question['options'])){
        $new_question->options = $question['options'];
      }else{
        $new_question->options = array();
      }
      $new_question->required = $question['required'];
      $new_question->sequence = $sequence;
      $new_question->save();
      return $new_question->id;
    }

    public function getAllFeedback()
    {
        if (!$this->account->hasChildPermission('adm/mship/feedback/list')) {
            abort(401, 'Unauthorized action.');
        }

        $feedback = FeedbackModel::with('account')->orderBy('created_at', 'desc')->get();

        return $this->viewMake('adm.mship.feedback.list')
                    ->with('feedback', $feedback);
    }

    public function getATCFeedback()
    {
        if (!$this->account->hasChildPermission('adm/mship/feedback/list/atc')) {
            abort(404, 'Unauthorized action.');
        }

        $feedback = FeedbackModel::with('account')->orderBy('created_at', 'desc')->atc()->get();

        return $this->viewMake('adm.mship.feedback.list')
                    ->with('feedback', $feedback);
    }

    public function getPilotFeedback()
    {
        if (!$this->account->hasPermission('adm/mship/feedback/list/pilot')) {
            abort(401, 'Unauthorized action.');
        }

        $feedback = FeedbackModel::with('account')->orderBy('created_at', 'desc')->pilot()->get();

        return $this->viewMake('adm.mship.feedback.list')
                    ->with('feedback', $feedback);
    }

    public function getViewFeedback(FeedbackModel $feedback)
    {
        if ($this->account->hasChildPermission('adm/mship/feedback/list')) {
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

    public function getActioned(FeedbackModel $feedback)
    {
        $conditions = [];
        $conditions[] = $this->account->hasChildPermission('adm/mship/feedback/list');
        $conditions[] = ($this->account->hasChildPermission('adm/mship/feedback/list/atc') && $feedback->isATC() == true);
        $conditions[] = ($this->account->hasChildPermission('adm/mship/feedback/list/pilot') && $feedback->isATC() == false);

        foreach($conditions as $condition){
          if($condition){
              $feedback->markActioned(\Auth::user());
              return Redirect::back()
                              ->withSuccess("Feedback marked as Actioned!");
          }
        }
        abort(401, 'Unauthorized action.');
    }
}
