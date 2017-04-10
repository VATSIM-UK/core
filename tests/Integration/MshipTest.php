<?php

namespace Tests\Unit;

use App\Models\Sys\Notification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MshipTest extends TestCase
{
    use DatabaseTransactions;
    
    /** @test */
    public function it_loads_ok(){
        $this->visit("/");

        $this->assertResponseOk();
    }

    /** @test **/
    public function it_redirects_to_the_landing_page_when_viewing_the_root_url_and_not_logged_in()
    {
        $this->visit("/");

        $this->seePageIs(route("mship.manage.landing"));
    }
    
    /** @test **/
    public function it_authenticates_a_user_post_vatsim_cert_return()
    {
        
    }
    
    /** @test **/
    public function it_determines_that_cert_is_offline_and_offers_alternative_login()
    {
        
    }

    /** @test */
    public function it_redirects_to_the_dashboard_when_viewing_the_root_url_when_logged_in_without_notifications_to_read(){
        Notification::getQuery()->delete();
        $account = factory(\App\Models\Mship\Account::class)->create();

        $this->actingAs($account);

        $this->visit("/");

        $this->assertResponseOk();
        $this->seePageIs(route("mship.manage.dashboard"));
    }

    /** @test */
    public function it_redirects_to_the_notifications_page_when_viewing_the_root_url_when_logged_in_with_must_read_notifications(){
        $account = factory(\App\Models\Mship\Account::class)->create();
        $mustReadNotification = factory(\App\Models\Sys\Notification::class, "must_read")->create();

        $this->actingAs($account);

        $this->visit("/");

        $this->assertResponseOk();
        $this->seePageIs(route("mship.notification.list"));
    }

    /** @test */
    public function it_redirects_to_the_notifications_page_when_viewing_the_root_url_when_logged_in_with_important_notifications(){
        $account = factory(\App\Models\Mship\Account::class)->create();
        $mustReadNotification = factory(\App\Models\Sys\Notification::class, "important")->create();

        $this->actingAs($account);

        $this->visit("/");

        $this->assertResponseOk();
        $this->seePageIs(route("mship.notification.list"));
    }

    /** @test */
    public function it_redirects_to_the_dashboard_even_though_a_general_notification_is_unread(){
        Notification::getQuery()->delete();
        $account = factory(\App\Models\Mship\Account::class)->create();
        $mustReadNotification = factory(\App\Models\Sys\Notification::class)->create();

        $this->actingAs($account);

        $this->visit("/");

        $this->assertResponseOk();
        $this->seePageIs(route("mship.manage.dashboard"));
    }
}