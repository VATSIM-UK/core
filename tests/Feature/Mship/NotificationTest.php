<?php

    namespace Tests\Feature\Mship;

use App\Models\Mship\Account;
    use App\Models\Sys\Notification;
    use Carbon\Carbon;
    use Illuminate\Foundation\Testing\DatabaseTransactions;
    use Tests\TestCase;

    class NotificationTest extends TestCase
    {
        use DatabaseTransactions;

        private $account;

        protected function setUp()
        {
            parent::setUp();
            $this->account = factory(Account::class)->create();
        }

        /** @test **/
        public function testLoggedInUserTakenToCompulsoryNotification()
        {
            $n = factory(Notification::class)->create(['status' => Notification::STATUS_MUST_ACKNOWLEDGE]);
            $this->actingAs($this->account)->get(route('mship.manage.dashboard'))->assertRedirect(route('mship.notification.list'));
        }

        /** @test **/
        public function testLoggedInUserNotForcedToNonCompulsoryNotification()
        {
            $n = factory(Notification::class)->create(['status' => Notification::STATUS_GENERAL]);
            $this->actingAs($this->account)->get(route('mship.manage.dashboard'))->assertOk();
        }

        /** @test **/
        public function testLoggedInUserCanReadNotification()
        {
            $n = factory(Notification::class)->create(['status' => Notification::STATUS_GENERAL]);
            $this->actingAs($this->account)->post(route('mship.notification.acknowledge', $n))->assertRedirect(route('mship.notification.list'));
        }

        /** @test **/
        public function testUserCantDoubleReadNotification()
        {
            $n = factory(Notification::class)->create(['status' => Notification::STATUS_GENERAL]);
            $this->account->readSystemNotifications()
                ->attach($n->id, ['created_at' => Carbon::now()]);
            $this->actingAs($this->account)->post(route('mship.notification.acknowledge', $n))->assertRedirect(route('mship.manage.dashboard'));
        }
    }
