<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private $account;
    private $accountOther;
    private $emailOther;

    public function setUp()
    {
        parent::setUp();

        // fakes notifications for the entire test class
        Notification::fake();

        $this->account = factory(\App\Models\Mship\Account::class)->create();

        $this->accountOther = factory(\App\Models\Mship\Account::class)->create();

        $this->emailOther = 'email@otheruser.co.uk';
    }

    /** @test * */
    public function testUserCanAccessEmailAddForm()
    {
        $this->actingAs($this->account)->get(route('mship.manage.email.add'))
            ->assertSuccessful();
    }

    /** @test * */
    public function testRedirectOnInvalidEmail()
    {
        $data = [
            'new_email' => 'not_an_email.com',
            'new_email2' => 'not_an_email.com',
        ];

        $this->actingAs($this->account)->post(route('mship.manage.email.add.post'), $data)
            ->assertRedirect(route('mship.manage.email.add'))
            ->assertSessionHas('error', 'You have entered an invalid email address.');
    }

    /** @test * */
    public function testRedirectOnNonMatchingEmail()
    {
        $data = [
            'new_email' => 'matching.email@example.com',
            'new_email2' => 'not.matching.email@example.com',
        ];

        $this->actingAs($this->account)->post(route('mship.manage.email.add.post'), $data)
            ->assertRedirect(route('mship.manage.email.add'))
            ->assertSessionHas('error', 'Emails entered are different.  You need to enter the same email, twice.');
    }

    /** @test * */
    public function testSuccessfulPostEmail()
    {
        $data = [
            'new_email' => 'email@example.com',
            'new_email2' => 'email@example.com',
        ];

        $this->actingAs($this->account)->post(route('mship.manage.email.add.post'), $data)
            ->assertRedirect(route('mship.manage.dashboard'))
            ->assertSessionHas('success');
    }

    /** @test * */
    public function testDuplicateEmailOnPost()
    {
        $account = $this->account->secondaryEmails()->create(['email' => 'email2@example.com']);

        $data = [
            'new_email' => 'email2@example.com',
            'new_email2' => 'email2@example.com',
        ];

        $this->actingAs($this->account->fresh())->post(route('mship.manage.email.add.post'), $data)
            ->assertRedirect(route('mship.manage.dashboard'))
            ->assertSessionHas('error', 'This email has already been added to your account.');
    }

    /** @test * */
    public function testRedirectOnSecondaryEmailDeleted()
    {
        $account = $this->account->secondaryEmails()->create(['email' => 'secondary.email@example.com']);

        $data = [
            'id' => $account->id,
        ];

        $this->actingAs($this->account->fresh())->post(route('mship.manage.email.delete.post', $account), $data)
            ->assertRedirect(route('mship.manage.dashboard'))
            ->assertSessionHas('success', 'Your secondary email ('.$account->email.') has been removed!');
    }

    /** @test * */
    public function testSuccessfulSecondaryEmailAddViaGetHasRelevantData()
    {
        $account = $this->account->secondaryEmails()->create(['email' => 'secondary.email@example.com']);

        $data = [
            'id' => $account->id,
        ];

        $this->actingAs($this->account->fresh())->get(route('mship.manage.email.delete.post', $account), $data)
            ->assertViewIs('mship.management.email.delete')
            ->assertViewHas(['email' => $account->email]);
    }

    /** @test * */
    public function testAssignmentsEmailsPassedToView()
    {
        $email = $this->account->fresh()->email;

        $this->actingAs($this->account)->get(route('mship.manage.email.assignments'))
            ->assertViewIs('mship.management.email.assignments')
            ->assertViewHas([
                'userPrimaryEmail' => $email,
                'userSecondaryVerified' => $this->account->verified_secondary_emails,
            ]);
    }

    /** @test * */
    public function testUserCannotDeleteOtherUsersEmail()
    {
        $emailInstance = $this->accountOther->secondaryEmails()->create(['email' => $this->emailOther]);

        $this->actingAs($this->account)->get(route('mship.manage.email.delete', $emailInstance))
            ->assertRedirect(route('mship.manage.dashboard'));
    }

    /** @test * */
    public function testUserCannotDeleteOtherUsersEmailOnPost()
    {
        $emailInstance = $this->accountOther->secondaryEmails()->create(['email' => $this->emailOther]);

        $this->actingAs($this->account)->post(route('mship.manage.email.delete.post', $emailInstance))
            ->assertRedirect(route('mship.manage.dashboard'));
    }

    /** @test * */
    public function testExistingAuthenticatedUserCanVerifyEmailViaGet()
    {
        // as the create() factory method uses save, this should generate a token automatically
        $email = factory(\App\Models\Mship\Account\Email::class)->states('unverified')->create();

        $this->actingAs($this->account)->get(route('mship.manage.email.verify', $email->tokens->first()))
            ->assertRedirect(route('mship.manage.dashboard'))
            ->assertSessionHas('success', 'Your new email address ('.$email->email.') has been verified!');
    }

    /** @test * */
    public function testExistingUnAuthenticatedUserCanVerifyEmailViaGet()
    {
        $email = factory(\App\Models\Mship\Account\Email::class)->states('unverified')->create();

        // ensures request is not sent by an authenticated user.
        $this->withoutMiddleware()->get(route('mship.manage.email.verify', $email->tokens->first()))
            ->assertViewIs('mship.management.email.verify')
            ->assertViewHas('success', 'Your new email address ('.$email->email.') has been verified!');
    }
}
