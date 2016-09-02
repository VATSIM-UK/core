<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class SiteApplicationTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_loads_ok()
    {
        $account = factory(App\Models\Mship\Account::class)->create();

        $this->actingAs($account)
            ->visit(route("visiting.landing"));

        $this->assertResponseOk();
    }

    /** @test */
    public function it_doesnt_allow_atc_visiting_if_there_are_no_places()
    {
        $account = factory(App\Models\Mship\Account::class)->create();

        $this->actingAs($account)
            ->visit(route("visiting.landing"))
            ->see("THERE ARE NO VISITING ATC PLACES");
    }

    /** @test */
    public function it_doesnt_allow_division_members_to_start_atc_visit_application()
    {
        $facility = factory(App\Modules\Visittransfer\Models\Facility::class, "atc_visit")->create();

        $account = factory(App\Models\Mship\Account::class)->create();

        $account->addState(\App\Models\Mship\State::findByCode("DIVISION"), "EUR", "GBR");

        $this->actingAs($account->fresh())
            ->visit(route("visiting.landing"))
            ->see("You are not able to apply to visit at this time.");
    }

    /** @test */
    public function it_doesnt_allow_new_atc_visit_to_be_started_if_atc_visit_is_open()
    {
        $facility = factory(App\Modules\Visittransfer\Models\Facility::class, "atc_visit")->create();

        $account = factory(App\Models\Mship\Account::class)->create();

        $application = factory(\App\Modules\Visittransfer\Models\Application::class, "atc_visit")->create([
            "account_id" => $account->id,
        ]);

        $this->actingAs($account->fresh())
            ->visit(route("visiting.landing"))
            ->see("CONTINUE APPLICATION")
            ->click("CONTINUE APPLICATION")
            ->seePageIs(route("visiting.application.facility", [$application->public_id]));
    }

    /** @test */
    public function it_allows_new_atc_visit_to_be_started_if_previous_atc_visit_is_closed()
    {
        $facility = factory(App\Modules\Visittransfer\Models\Facility::class, "atc_visit")->create();

        $account = factory(App\Models\Mship\Account::class)->create();
        $account->addState(\App\Models\Mship\State::findByCode("DIVISION"), "EUR", "GBR");

        $application = factory(\App\Modules\Visittransfer\Models\Application::class, "atc_visit")->create([
            "account_id" => $account->id,
            "status" => \App\Modules\Visittransfer\Models\Application::STATUS_WITHDRAWN,
        ]);

        $this->actingAs($account->fresh())
            ->visit(route("visiting.landing"))
            ->see("START ATC APPLICATION");
    }

    /** @test */
    public function it_doesnt_allow_new_atc_visit_to_be_started_if_atc_transfer_is_open()
    {
        $facility = factory(App\Modules\Visittransfer\Models\Facility::class, "atc_visit")->create();

        $account = factory(App\Models\Mship\Account::class)->create();
        $account->addState(\App\Models\Mship\State::findByCode("DIVISION"), "EUR", "GBR");

        $application = factory(\App\Modules\Visittransfer\Models\Application::class, "atc_transfer")->create([
            "account_id" => $account->id,
        ]);

        $this->actingAs($account)
            ->visit(route("visiting.landing"))
            ->see("You currently have a transfer application open.");
    }

    /** @test */
    public function it_allows_new_atc_visit_to_be_started_if_previous_atc_transfer_is_closed()
    {
        $facility = factory(App\Modules\Visittransfer\Models\Facility::class, "atc_visit")->create();

        $account = factory(App\Models\Mship\Account::class)->create();
        $account->addState(\App\Models\Mship\State::findByCode("DIVISION"), "EUR", "GBR");

        $application = factory(\App\Modules\Visittransfer\Models\Application::class, "atc_transfer")->create([
            "account_id" => $account->id,
            "status" => \App\Modules\Visittransfer\Models\Application::STATUS_WITHDRAWN,
        ]);

        $this->actingAs($account)
            ->visit(route("visiting.landing"))
            ->see("START ATC APPLICATION");
    }

    /** @test */
    public function it_allows_non_division_members_to_start_atc_visit_application()
    {
        $facility = factory(App\Modules\Visittransfer\Models\Facility::class, "atc_visit")->create();

        $account = factory(App\Models\Mship\Account::class)->create();

        $account->addState(\App\Models\Mship\State::findByCode("REGION"), "USA-N", "JFK");

        $this->actingAs($account)
            ->visit(route("visiting.landing"))
            ->see("START ATC APPLICATION")
            ->click("START ATC APPLICATION")
            ->seePageIs(route("visiting.application.start", [
                \App\Modules\Visittransfer\Models\Application::TYPE_VISIT,
                'atc'
            ]));
    }

    /** @test */
    public function it_allows_atc_visit_application_to_accept_terms_and_create_application()
    {
        $facility = factory(App\Modules\Visittransfer\Models\Facility::class, "atc_visit")->create();

        $account = factory(App\Models\Mship\Account::class)->create();

        $this->actingAs($account)
            ->visit(route("visiting.application.start", [
                \App\Modules\Visittransfer\Models\Application::TYPE_VISIT,
                'atc',
            ]))
            ->dontSee("terms_not_staff")
            ->check("terms_read")
            ->check("terms_one_hour")
            ->check("terms_hours_minimum")
            ->check("terms_hours_minimum_relevant")
            ->check("terms_recent_transfer")
            ->check("terms_90_day")
            ->press("START APPLICATION")
            ->seePageIs(route("visiting.application.facility", [$account->visit_transfer_current->public_id]));

        $this->seeInDatabase("vt_application", [
            "account_id" => $account->id,
        ]);
    }

    /** @test */
    public function it_displays_atc_visit_applicant_errors_when_missing_terms()
    {
        $facility = factory(App\Modules\Visittransfer\Models\Facility::class, "atc_visit")->create([

        ]);

        $account = factory(App\Models\Mship\Account::class)->create();

        $this->actingAs($account)
            ->visit(route("visiting.application.start", [
                \App\Modules\Visittransfer\Models\Application::TYPE_VISIT,
                'atc',
            ]))
            ->press("START APPLICATION")
            ->see("You are required to read the VTCP.")
            ->see("You must agree to complete your application within 1 hour.")
            ->see("You must confirm that you have the minimum number of hours at your present rating.")
            ->see("The hours you have achieved, must be at a relevant rating.")
            ->see("You are only permitted to visit/transfer once every 90 days.")
            ->see("You must agree that you will be returned to your former region/division if you do not complete your induction training.")
            ->see("You must agree to complete your application within 1 hour.");

        $this->notSeeInDatabase("vt_application", [
            "account_id" => $account->id,
        ]);
    }

    /** @test */
    public function it_displays_atc_visit_with_no_training_facility_to_be_selected()
    {
        $facility = factory(App\Modules\Visittransfer\Models\Facility::class, "atc_visit")->create([
            "training_required" => false,
        ]);

        $account = factory(App\Models\Mship\Account::class)->create();

        $application = factory(App\Modules\Visittransfer\Models\Application::class)->create([
            "account_id" => $account->id,
        ]);

        $this->actingAs($account)
            ->visit(route("visiting.application.facility", [$application->public_id]))
            ->see("APPLY TO THIS FACILITY")
            ->click("APPLY TO THIS FACILITY")
            ->seePageIs(route("visiting.application.statement", [$application->public_id]));

        $this->assertAttributeEquals($facility->id, "facility_id", $application->fresh());
    }

    /** @test */
    public function it_displays_atc_visit_with_training_facility_to_be_selected()
    {
        $facility = factory(App\Modules\Visittransfer\Models\Facility::class, "atc_visit")->create([
            "training_required" => true,
            "training_spaces" => null,
        ]);

        $account = factory(App\Models\Mship\Account::class)->create();

        $application = factory(App\Modules\Visittransfer\Models\Application::class)->create([
            "account_id" => $account->id,
        ]);

        $this->actingAs($account)
            ->visit(route("visiting.application.facility", [$application->public_id]))
            ->see("APPLY TO THIS FACILITY")
            ->click("APPLY TO THIS FACILITY")
            ->seePageIs(route("visiting.application.statement", [$application->public_id]));

        $this->assertAttributeEquals($facility->id, "facility_id", $application->fresh());
    }

    /** @test */
    public function it_displays_atc_visit_with_no_training_spaces_available_facility_not_to_be_selected()
    {
        $facility = factory(App\Modules\Visittransfer\Models\Facility::class, "atc_visit")->create([
            "training_required" => true,
            "training_spaces" => 0,
        ]);

        $account = factory(App\Models\Mship\Account::class)->create();

        $application = factory(App\Modules\Visittransfer\Models\Application::class)->create([
            "account_id" => $account->id,
        ]);

        $this->actingAs($account)
            ->visit(route("visiting.application.facility", [$application->public_id]))
            ->see("NO PLACES AVAILABLE");

        $this->assertAttributeNotEquals($facility->id, "facility_id", $application->fresh());
    }

    /** @test */
    public function it_doesnt_display_references_table_to_applicants()
    {
        $account = factory(App\Models\Mship\Account::class)->create();

        $this->actingAs($account)
            ->visit(route("visiting.landing"))
            ->dontSee("Pending References");
    }

//
//    /** @test **/
//    public function it_redirects_to_the_landing_page_when_viewing_the_root_url_and_not_logged_in()
//    {
//        $this->visit("/");
//
//        $this->seePageIs(route("mship.manage.landing"));
//    }
//
//    /** @test **/
//    public function it_authenticates_a_user_post_vatsim_cert_return()
//    {
//
//    }
//
//    /** @test **/
//    public function it_determines_that_cert_is_offline_and_offers_alternative_login()
//    {
//
//    }
//
//    /** @test */
//    public function it_redirects_to_the_dashboard_when_viewing_the_root_url_when_logged_in_without_notifications_to_read(){
//        Notification::getQuery()->delete();
//        $account = factory(\App\Models\Mship\Account::class)->create();
//
//        $this->actingAs($account);
//
//        $this->visit("/");
//
//        $this->assertResponseOk();
//        $this->seePageIs(route("mship.manage.dashboard"));
//    }
//
//    /** @test */
//    public function it_redirects_to_the_notifications_page_when_viewing_the_root_url_when_logged_in_with_must_read_notifications(){
//        $account = factory(\App\Models\Mship\Account::class)->create();
//        $mustReadNotification = factory(\App\Models\Sys\Notification::class, "must_read")->create();
//
//        $this->actingAs($account);
//
//        $this->visit("/");
//
//        $this->assertResponseOk();
//        $this->seePageIs(route("mship.notification.list"));
//    }
//
//    /** @test */
//    public function it_redirects_to_the_notifications_page_when_viewing_the_root_url_when_logged_in_with_important_notifications(){
//        $account = factory(\App\Models\Mship\Account::class)->create();
//        $mustReadNotification = factory(\App\Models\Sys\Notification::class, "important")->create();
//
//        $this->actingAs($account);
//
//        $this->visit("/");
//
//        $this->assertResponseOk();
//        $this->seePageIs(route("mship.notification.list"));
//    }
//
//    /** @test */
//    public function it_redirects_to_the_dashboard_even_though_a_general_notification_is_unread(){
//        Notification::getQuery()->delete();
//        $account = factory(\App\Models\Mship\Account::class)->create();
//        $mustReadNotification = factory(\App\Models\Sys\Notification::class)->create();
//
//        $this->actingAs($account);
//
//        $this->visit("/");
//
//        $this->assertResponseOk();
//        $this->seePageIs(route("mship.manage.dashboard"));
//    }
}