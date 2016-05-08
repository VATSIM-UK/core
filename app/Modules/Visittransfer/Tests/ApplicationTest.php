<?php


use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApplicationTest extends TestCase {
    use DatabaseTransactions;

    /** Unit Testing */
    
    /** @test */
    public function it_can_create_a_new_application_for_a_user(){
        $application = null;
    }

    /** @test */
    public function it_throws_an_exception_when_attempting_to_create_a_duplicate_application(){
        $application = null;
    }

    /** @test */
    public function it_throws_an_exception_when_attempting_to_create_an_application_for_a_division_member(){

    }

    /** Application Testing */
    
    /** @test */
    public function it_displays_the_dashboard_to_the_member(){
        
    }

    /** @test */
    public function it_displays_the_start_new_visiting_application_button_to_a_none_division_member(){
        
    }
    
    /** @test */
    public function it_does_not_display_the_start_new_visiting_application_button_to_a_division_member(){
        
    }
    
    /** @test */
    public function it_does_not_display_the_new_application_buttons_to_those_with_an_open_application(){

    }
    
    /** @test */
    public function it_displays_the_start_new_transferring_application_button_to_a_none_division_member(){
        
    }

    /** @test */
    public function it_does_not_display_the_start_new_transferring_button_to_a_division_member(){
        
    }
}