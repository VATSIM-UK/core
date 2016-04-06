<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class MshipTest extends TestCase
{
    use DatabaseTransactions;

    /** @test * */
    public function it_redirects_to_the_landing_page_when_viewing_the_root_url()
    {
        $this->visit("/")
             ->seePageIs(route("mship.manage.landing"));
    }
    
    /** @test **/
    public function it_authenticates_a_user_post_vatsim_cert_return()
    {
        
    }
    
    /** @test **/
    public function it_determines_that_cert_is_offline_and_offers_alternative_login()
    {
        
    }

    /** @test **/
    public function it_displays_the_members_dashboard()
    {

    }

    /** @test **/
    public function it_redirects_away_from_the_dashboard_if_the_member_isnt_logged_in()
    {

    }
}