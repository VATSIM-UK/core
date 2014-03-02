<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Training_Theory extends Controller_Training_Master {

    public function before() {
        parent::before();
    }

    /**
     * Display a list of all tests in the database currently.
     */
    public function action_admin_test_list() {
        $tests = ORM::factory("Training_Theory_Test")->find_all();
        $this->_data["tests"] = $tests;
        $this->setTitle("Theory :: Test List");
    }

    /**
     * Create/Edit a test.
     */
    public function action_admin_test_modify() {
        $testID = $this->request->param("id");
        $test = ORM::factory("Training_Theory_Test", $testID);
        $create = ($testID == 0) ? true : false;
        
        // Exists? No?
        if (!$test->loaded() && !$create) {
            $this->setMessage("Test Unavailable", "This test couldn't be found.  Please try again.", "error");
            $this->redirect("training/theory/admin_test_list");
            return false;
        }

        if (HTTP_Request::POST == $this->request->method()) {
            // What about the categories?
            $cats = array();
            foreach($this->request->post("category_id") as $key => $id){
                if($id==0) continue;
                $c = array();
                $c["category_id"] = $id;
                $c["question_count"] = Arr::get($this->request->post("category_question_count"), $key);
                $c["difficulty_min"] = Arr::get($this->request->post("category_difficulty_min"), $key);
                $c["difficulty_max"] = Arr::get($this->request->post("category_difficulty_max"), $key);
                $cats[] = $c;
            }
            
            // Options for create/edit.
            $opt = array();
            $opt["time_allowed"] = $this->request->post("time_allowed");
            $opt["time_expire_action"] = $this->request->post("time_expire_action");
            $opt["retake_cooloff"] = $this->request->post("retake_cooloff");
            $opt["retake_max"] = $this->request->post("retake_max");
            if ($create) {
                $test = ORM::factory("Training_Theory_Test")->add_test($this->request->post("name"), $opt);
                $test->edit_test_categories($cats);
                $this->setMessage("Test Created", "Test '".$test->name."' was created succesfully.  You can now edit the details below.", "success");
                $this->redirect("training/theory/admin_test_modify/".$test->id);
                return true;
            } else {
                $test->edit_test($opt);
                $test->edit_test_categories($cats);
                $this->setMessage("Test Edited", "Test '".$test->name."' was edited succesfully.", "success");
            }
        }

        $this->_data["create"] = $create;
        $this->_data["test"] = $test;
        $categories = ORM::factory("Training_Theory_Category")->order_by("name", "ASC")->find_all();
        $this->_data["categories"] = $categories;

        if ($create) {
            $this->setTitle("Theory :: Create New Test");
        } else {
            $this->setTitle("Theory :: Edit Test #" . $test->id.", ".$test->name);
        }
    }

    /**
     * Toggle the status of a test.
     */
    public function action_admin_test_toggle_status() {
        $testID = $this->request->param("id");
        $test = ORM::factory("Training_Theory_Test", $testID);

        // Exists? No?
        if (!$test->loaded()) {
            $this->setMessage("Test Unavailable", "This test couldn't be found.  Please try again.", "error");
            $this->redirect("training/theory/admin_test_list");
            return false;
        }

        // Now toggle!
        $_str = (!$test->available ? "Enabled" : "Disabled");
        $this->setMessage("Test " . $_str, "You have successfully <strong>" . $_str . "</strong> the '" . $test->name . "' test.", "success");
        $test->edit_test(array("available" => !$test->available));
        $this->redirect("training/theory/admin_test_list");
        return;
    }

}
