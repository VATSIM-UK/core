<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailAssignmentTest extends TestCase
{
    use DatabaseTransactions;

    private $account;

    public function setUp()
    {
        parent::setUp();

        $this->account = factory(\App\Models\Mship\Account::class)->create();
    }

    /** @test * */
    public function testRedirectOnInvalidEmail()
    {
        $data = [
            'new_email' => 'not_an_email.com',
            'new_email2' => 'not_an_email.com'
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
            'new_email2' => 'not.matching.email@example.com'
        ];

        $this->actingAs($this->account)->post(route('mship.manage.email.add.post'), $data)
            ->assertRedirect(route('mship.manage.email.add'))
            ->assertSessionHas('error', 'Emails entered are different.  You need to enter the same email, twice.');
    }

    /** @test **/
    public function testRedirectOnSecondaryEmailDeleted()
    {
        // prevents email being sent.
        Notification::fake();

        $account = $this->account->secondaryEmails()->create(['email' => 'secondary.email@example.com']);

        $data = [
            'id' => $account->id,
        ];

        $this->actingAs($this->account->fresh())->post(route('mship.manage.email.delete.post', $account), $data)
            ->assertRedirect(route('mship.manage.dashboard'))
            ->assertSessionHas('success', 'Your secondary email ('.$account->email.') has been removed!');

    }
}
