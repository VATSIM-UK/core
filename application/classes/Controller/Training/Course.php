<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Training_Course extends Controller_Training_Master {
    public function before() {
        parent::before();
    }
    
    /**
     * Dynamic function: if no id is specified, or it doesn't exist, display a list.
     * 
     * Otherwise, display the course info!
     */
    public function action_dynamic_display(){
        $courseID = $this->_area;
        $course = ORM::factory("Training_Course", $courseID);
        
        if($course && $course->loaded()){
            $this->action_display($courseID);
            return;
        }
        
        $this->action_list();
    }
    
    /**
     * Display a list of all the available course (regardless of user level).
     */
    public function action_list(){
    }
}