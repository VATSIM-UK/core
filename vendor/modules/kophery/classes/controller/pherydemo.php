<?php
class Controller_PheryDemo extends Controller {
	
	public function before() 
	{
		parent::before();
		
		// The following quickinit method registers 'ph_' prefixed functions as phery callbacks: 
		Phery_Utils::quickinit($this);		
	}	
	
	public function action_index() 
	{
		$view = View::factory('phery/demo');
		$this->response->body($view);
	}
	
	//-------------------------------------------------------------------------------------------------------------
	
	// This method gets registered as phery callback by Phery_Utils::quickinit method
	public function ph_testmethod($data) 
	{
		// Phery allows jquery methods to be used from php! :-)
		return phery_response::factory('#target')
		->html(print_r($data['somedata'], true))
		->css(array('backgroundColor' => 'yellow'))
		->animate(array('width'=>'600'), 'slow')
		->height('200')
		;	
	}	

	public function ph_testmethod2($data) 
	{
		return phery_response::factory('#target')
		->html(print_r($data['somedata'], true))
		->css(array('backgroundColor' => 'lime'))
		->width('300')
		->height('100')
		->alert(print_r($data['moredata'], true))
		;	
	}	
	
	
}