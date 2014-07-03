<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Training_Theory_Question_Admin extends Controller_Training_Master {
    private $_questionID = 0;
    private $_question = null;
    
    public function before(){
        parent::before();
        $this->_questionID = $this->request->param("id");
        $this->_question = ORM::factory("Training_Theory_Question", $this->_questionID);
    }
    
    public function after(){
        $this->_data["question"] = $this->_question;
        
        parent::after();
    }
    
    /**
     * Display a list of all tests in the database currently.
     */
    public function action_list() {
        $questions = ORM::factory("Training_Theory_Question")->get_all_questions();
        $this->_data["questions"] = $questions;
        $this->setTitle("Theory :: Question List");
    }

    /**
     * Create/Edit a test.
     */
    public function action_modify() {
        $create = ($this->_questionID == 0) ? true : false;
        
        // Exists? No?
        if ((!$this->_question->loaded() OR $this->_question->deleted) && !$create) {
            $this->setMessage("Question Unavailable", "This question couldn't be found.  Please try again.", "error");
            $this->redirect("training/theory_question_admin/list");
            return false;
        }
        
        // If creating, give it a question type!
        if($create){
            if($this->request->query("questionType") != NULL && Enum_Training_Theory_Question_Type::valueExists($this->request->query("questionType"))){
                $this->_question->type = $this->request->query("questionType");
            } else {
                $this->_question->type = Enum_Training_Theory_Question_Type::DEFAULT_TYPE;
            }
        }

        if (HTTP_Request::POST == $this->request->method()) {
            
            
            
            // Options for create/edit.
            $opt = array();
            $opt["type"] = $this->request->post("type");
            $opt["category_id"] = $this->request->post("category_id");
            $opt["difficulty_rating"] = $this->request->post("difficulty_rating");
            
            // Now get options specific to the question type.
            $QtypeHelper = "Helper_Training_Theory_Question_Type_".ucfirst(strtolower(Enum_Training_Theory_Question_Type::valueToType($opt["type"])));
            $opt = $opt + $QtypeHelper::prepareOptions($this->request);
            
            if ($create) {
                $test = ORM::factory("Training_Theory_Question")->add_question($opt);
                $this->setMessage("Question Created", "Question #".$this->_question->id." was created succesfully.", "success");
                $this->redirect("training/theory_question_admin/list/");
                return true;
            } else {
                $this->_question = $this->_question->edit_question($opt);
                $this->setMessage("Question Edited", "Question #'".$this->_question->id."' was edited succesfully.", "success");
            }
        }
        
        $this->_data["create"] = $create;
        $categories = ORM::factory("Training_Category")->order_by("name", "ASC")->find_all_categories();
        $this->_data["categories"] = $categories;

        if ($create) {
            $this->setTitle("Theory :: Create New Question");
        } else {
            $this->setTitle("Theory :: Edit Question #" . $this->_question->id.", ".$this->_question->question);
        }
    }
    
    public function action_modify_type(){
        $qType = $this->request->query("questionType");
        $qType = (is_numeric($qType) && Enum_Training_Theory_Question_Type::valueExists($qType)) ? $qType : Enum_Training_Theory_Question_Type::DEFAULT_TYPE;
        $qType = ucfirst(strtolower(Enum_Training_Theory_Question_Type::valueToType($qType)));
        
        $this->_wrapper = FALSE;
        $this->setTemplate("training/theory/question/admin/modify/types/".$qType);
    }

    /**
     * Toggle the status of a test.
     */
    public function action_toggle_status() {
        $this->_questionID = $this->request->param("id");
        $question = ORM::factory("Training_Theory_Question", $this->_questionID);

        // Exists? No?
        if (!$question->loaded() OR $question->deleted) {
            $this->setMessage("Question Unavailable", "This question couldn't be found.  Please try again.", "error");
            $this->redirect("training/theory_question_admin/list");
            return false;
        }

        // Now toggle!
        $_str = (!$question->available ? "Enabled" : "Disabled");
        $this->setMessage("Question " . $_str, "You have successfully <strong>" . $_str . "</strong> the '" . $question->question . "' question.", "success");
        $question->edit_question(array("available" => !$question->available));
        $this->redirect("training/theory_question_admin/list");
        return;
    }
    
    /**
     * Delete a test.
     */
    public function action_delete(){
        $this->_questionID = $this->request->param("id");
        $question = ORM::factory("Training_Theory_Question", $this->_questionID);

        // Exists? No?
        if (!$question->loaded() OR $question->deleted) {
            $this->setMessage("Question Unavailable", "This question couldn't be found.  Please try again.", "error");
            $this->redirect("training/theory_question_admin/list");
            return false;
        }

        // Now delete!
        $this->setMessage("Question Deleted", "You have successfully <strong>deleted</strong> the '" . $question->question . "' question.", "success");
        $question->edit_question(array("deleted" => 1));
        $this->redirect("training/theory_question_admin/list");
        return;
    }

}
