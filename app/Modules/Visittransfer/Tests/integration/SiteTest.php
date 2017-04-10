<?php


use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApplicationTest extends TestCase
{
    use DatabaseTransactions;
    /** Application Testing */

    /** @test */
    public function it_displays_the_dashboard_to_the_member()
    {
    }

    /** @test */
    public function it_displays_the_start_new_visiting_application_button_to_a_none_division_member()
    {
    }

    /** @test */
    public function it_does_not_display_the_start_new_visiting_application_button_to_a_division_member()
    {
    }

    /** @test */
    public function it_does_not_display_the_new_application_buttons_to_those_with_an_open_application()
    {
    }

    /** @test */
    public function it_displays_the_start_new_transferring_application_button_to_a_none_division_member()
    {
    }

    /** @test */
    public function it_does_not_display_the_start_new_transferring_button_to_a_division_member()
    {
    }
}
