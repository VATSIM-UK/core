<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Membership_Manage extends Controller_Membership_Master {
    protected $_permissions = array(
        "_" => array('*'),
    );
    
    public function getDefaultAction(){
        return "user";
    }

    public function before() {
        parent::before();

        // Add to the breadcrumb
        $this->addBreadcrumb("Manage", "manage");
    }

    public function after() {
        parent::after();
    }

    public function action_user (){
        $this->setTitle("Find User");
        $this->setTemplate("Manage/User");
        $this->addBreadcrumb("Find User", "user");
    }
    
    public function action_search(){
        $this->ajax_search();
    }
    
    public function ajax_search(){
        $search = ORM::factory('Account');
        
        if ($this->request->post('cid')){
            $search = $search->where('id', 'LIKE', '%'.$this->request->post('cid').'%');
        }
        
        if ($this->request->post('name')){
            $search = $search->where(DB::expr("CONCAT( name_first, ' ', name_last )"), 'LIKE', '%'.$this->request->post('name').'%');
        }
        
        if ($this->request->post('email')){
            /*
             * Ant - how to search by something else - i.e. would be a join but using model?
             */
        }
        
        $search = $search->find_all();
        $total = count($search);
        $total_pages = ceil($total/20);
        if ($this->request->post('page')){
            $page = intval($this->request->post('page'));
            if ($page<$total_pages && $page>0){
                $start_record = $page*20;
            } else {
                $page = 1;
            }
        } else {
            $page = 1;
        }
        
        if (!isset($start_record)){
            $start_record = 1;
        }
        
        //collector array of search matches
        $users = array();
        //an additional row containing the page information
        $users['page'] = array(
            'total'=>$total_pages,
            'current'=>$page,
            'results'=>$total
        );
        
        //table header information
        $users['header']=array('CID', 'Name', 'Status', 'State', 'ATC', 'Pilot');
        
        $k=0;
        $start_count = 0;
        foreach ($search as $match){
            $k++;
            if ($k>=$start_record){
                //create 
                
                $users[$match->id] = array(
                    $match->name_first.' '.$match->name_last,
                    'Status',
                    'State',
                    Enum_Account_Qualification_ATC::getDescription($match->get_atc_qualification()),
                    Enum_Account_Qualification_Pilot::getDisplayString($match->get_pilot_qualifications())
                );
                $start_count++;
                if ($start_count>=20){
                    break;
                }
            }
            
            
        }
        
        $this->_wrapper = false;
        $this->response->body(json_encode($users));
    }
    
    public function ajax_details(){
        $this->_wrapper = false;
        $this->setTemplate("Manage/Ajax/Details");
    }
    
    public function ajax_record(){
        $this->_wrapper = false;
        $this->setTemplate("Manage/Ajax/Record");
    }
    
    public function ajax_teamspeak(){
        $this->_wrapper = false;
        $this->setTemplate("Manage/Ajax/TeamSpeak");
    }
    
}