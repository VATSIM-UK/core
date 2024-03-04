<?php

namespace Tests\Feature\Account;

use App\Events\Mship\AccountAltered;
use App\Models\Mship\Account\Email;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailAssignmentTest extends TestCase
{
    use DatabaseTransactions;

    private $userOther;

    private $emailOther;

    public function setUp(): void
    {
        parent::setUp();

        // Fake notifications
        Notification::fake();

        $this->userOther = \App\Models\Mship\Account::factory()->create();

        $this->emailOther = 'email@otheruser.co.uk';
    }

    /** @test */
    public function testUserCanAccessEmailAddForm()
    {
        $this->actingAs($this->user)
            ->get(route('mship.manage.email.add'))
            ->assertSuccessful();
    }

    /** @test */
    public function testRedirectOnInvalidEmail()
    {
        $data = [
            'new_email' => 'not_an_email.com',
            'new_email2' => 'not_an_email.com',
        ];

        $this->actingAs($this->user)
            ->post(route('mship.manage.email.add.post'), $data)
            ->assertRedirect(route('mship.manage.email.add'))
            ->assertSessionHas('error', 'You have entered an invalid email address.');
    }

    /** @test */
    public function testRedirectOnNonMatchingEmail()
    {
        $data = [
            'new_email' => 'matching.email@example.com',
            'new_email2' => 'not.matching.email@example.com',
        ];

        $this->actingAs($this->user)
            ->post(route('mship.manage.email.add.post'), $data)
            ->assertRedirect(route('mship.manage.email.add'))
            ->assertSessionHas('error', 'Emails entered are different.  You need to enter the same email, twice.');
    }

    /** @test */
    public function testSuccessfulPostEmail()
    {
        $data = [
            'new_email' => 'email@example.com',
            'new_email2' => 'email@example.com',
        ];

        $this->actingAs($this->user)
            ->post(route('mship.manage.email.add.post'), $data)
            ->assertRedirect(route('mship.manage.dashboard'))
            ->assertSessionHas('success');
    }

    /** @test */
    public function testDuplicateEmailOnPost()
    {
        $this->user->secondaryEmails()->create(['email' => 'email2@example.com']);

        $data = [
            'new_email' => 'email2@example.com',
            'new_email2' => 'email2@example.com',
        ];

        $this->actingAs($this->user->fresh())
            ->post(route('mship.manage.email.add.post'), $data)
            ->assertRedirect(route('mship.manage.dashboard'))
            ->assertSessionHas('error', 'This email has already been added to your account.');
    }

    /** @test */
    public function testRedirectOnSecondaryEmailDeleted()
    {
        $account = $this->user->secondaryEmails()->create(['email' => 'secondary.email@example.com']);

        $data = [
            'id' => $account->id,
        ];

        $this->actingAs($this->user->fresh())
            ->post(route('mship.manage.email.delete.post', $account), $data)
            ->assertRedirect(route('mship.manage.dashboard'))
            ->assertSessionHas('success', 'Your secondary email ('.$account->email.') has been removed!');
    }

    /** @test */
    public function testSuccessfulSecondaryEmailAddViaGetHasRelevantData()
    {
        $account = $this->user->secondaryEmails()->create(['email' => 'secondary.email@example.com']);

        $data = [
            'id' => $account->id,
        ];

        $this->actingAs($this->user->fresh())
            ->get(route('mship.manage.email.delete.post', $account), $data)
            ->assertSee($account->email);
    }

    /** @test */
    public function testAssignmentsEmailsPassedToView()
    {
        $verifiedEmailAddress = 'my-verified-email@foo.com';
        $unverifiedEmailAddress = 'my-unverified-email@bar.com';

        $verifiedEmail = $this->user->secondaryEmails()->create([
            'email' => $verifiedEmailAddress,
        ]);
        $verifiedEmail->verify();

        $this->user->secondaryEmails()->create([
            'email' => $unverifiedEmailAddress,
        ]);

        $mainEmail = $this->user->fresh()->email;

        $this->actingAs($this->user->fresh())
            ->get(route('mship.manage.email.assignments'))
            ->assertSee($mainEmail)
            ->assertSee($verifiedEmail)
            ->assertDontSee($unverifiedEmailAddress);
    }

    /** @test */
    public function testUserCannotDeleteOtherUsersEmail()
    {
        $emailInstance = $this->userOther->secondaryEmails()->create(['email' => $this->emailOther]);

        $this->actingAs($this->user)->get(route('mship.manage.email.delete', $emailInstance))
            ->assertRedirect(route('mship.manage.dashboard'));
    }

    /** @test */
    public function testUserCannotDeleteOtherUsersEmailOnPost()
    {
        $emailInstance = $this->userOther->secondaryEmails()->create(['email' => $this->emailOther]);

        $this->actingAs($this->user)->post(route('mship.manage.email.delete.post', $emailInstance))
            ->assertRedirect(route('mship.manage.dashboard'));
    }

    /** @test */
    public function testExistingAuthenticatedUserCanVerifyEmailViaGet()
    {
        $email = factory(Email::class)->states('unverified')->create();

        $this->actingAs($this->user)
            ->get(route('mship.manage.email.verify', $email->tokens->first()))
            ->assertRedirect(route('mship.manage.dashboard'))
            ->assertSessionHas('success', 'Your new email address ('.$email->email.') has been verified!');
    }

    /** @test */
    public function testItTriggersAnUpdateWhenAssigningSSOEmail()
    {
        $sso_client = factory(\Laravel\Passport\Client::class)->create();
        $email = factory(Email::class)->create(['account_id' => $this->user->id]);

        $initialDispatcher = Event::getFacadeRoot();
        Event::fake();
        Model::setEventDispatcher($initialDispatcher);

        $this->actingAs($this->user)
            ->post(route('mship.manage.email.assignments.post', ['assign_'.$sso_client->id => $email->id]));

        Event::assertDispatched(AccountAltered::class);
    }

    /** @test */
    public function testItTriggersAnUpdateWhenUnAssigningSSOEmail()
    {
        $sso_email = factory(\App\Models\Sso\Email::class)->create();

        $initialDispatcher = Event::getFacadeRoot();
        Event::fake();
        Model::setEventDispatcher($initialDispatcher);

        $this->actingAs($this->user)
            ->post(route('mship.manage.email.assignments.post', ['assign_'.$sso_email->ssoAccount->id => 'pri']));

        Event::assertDispatched(AccountAltered::class);
    }
}
