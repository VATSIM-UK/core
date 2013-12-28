<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Training_Course extends Controller_Training_Master {
    public function before() {
        parent::before();
    }
    
    /**
     * Display a list of all the available course (regardless of user level).
     */
    public function action_list(){
        $this->setTitle("Current Training Courses");
        $this->_data["courses"] = ORM::factory("Training_Course")->getActive();
    }
    
    /**
     * Display a course.
     */
    public function action_display(){
        $courseID = $this->request->param("id");
        $course = ORM::factory("Training_Course", $courseID);
        $this->_data["course"] = $course;
        $this->setTitle("Course :: ".$course);
    }
}