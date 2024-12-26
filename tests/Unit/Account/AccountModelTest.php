<?php

namespace Tests\Unit\Account;

use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use App\Models\Roster;
use App\Notifications\Mship\EmailVerification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccountModelTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user->update([
            'name_first' => 'John',
            'name_last' => 'Doe',
            'email' => 'i_sleep@gmail.com',
        ]);

        Notification::fake();
    }

    /** @test */
    public function it_stores_basic_member_data()
    {
        $this->assertDatabaseHas('mship_account', [
            'name_first' => $this->user->name_first,
            'name_last' => $this->user->name_last,
            'email' => $this->user->email,
        ]);

        $this->assertTrue($this->user->exists);
    }

    /** @test */
    public function it_correctly_formats_names()
    {
        $this->user->update([
            'name_first' => 'mary',
            'name_last' => 'JANE',
        ]);

        $this->assertEquals('Mary', $this->user->name_first);
        $this->assertEquals('Jane', $this->user->name_last);

        $this->assertDatabaseHas('mship_account', [
            'id' => $this->user->id,
            'name_first' => 'Mary',
            'name_last' => 'Jane',
        ]);
    }

    /** @test */
    public function it_correctly_determines_if_the_member_name_is_a_valid_display_name()
    {
        $this->assertTrue($this->user->isValidDisplayName($this->user->real_name));
    }

    /** @test */
    public function it_correctly_determines_if_the_nickname_is_a_valid_display_name()
    {
        $this->user->nickname = 'Delboy';
        $this->user->save();

        $this->assertDatabaseHas('mship_account', [
            'id' => $this->user->id,
            'nickname' => 'Delboy',
        ]);

        $fullNickname = 'Delboy '.$this->user->name_last;
        $this->assertTrue($this->user->isValidDisplayName($fullNickname));
    }

    /** @test */
    public function it_allows_valid_incremented_nicknames()
    {
        $this->assertTrue($this->user->isDuplicateDisplayName("{$this->user->name_first} {$this->user->name_last}1"));
        $this->assertTrue($this->user->isDuplicateDisplayName("{$this->user->name_first} {$this->user->name_last}5"));
        $this->assertFalse($this->user->isDuplicateDisplayName("Joe {$this->user->name_last}5"));
    }

    /** @test */
    public function it_determines_that_a_name_is_still_valid_even_with_a_nickname_set()
    {
        $this->user->nickname = 'Delboy';
        $this->user->save();

        $this->assertDatabaseHas('mship_account', [
            'id' => $this->user->id,
            'nickname' => 'Delboy',
        ]);

        $this->assertTrue($this->user->isValidDisplayName($this->user->real_name));
    }

    /** @test */
    public function it_determines_when_there_is_an_invalid_display_name()
    {
        $this->user->nickname = 'Delboy';
        $this->user->name_last = 'Trotter';
        $this->user->save();

        $this->assertDatabaseHas('mship_account', [
            'id' => $this->user->id,
            'nickname' => 'Delboy',
        ]);

        $fullNickname = 'Rodney '.$this->user->name_last;
        $this->assertFalse($this->user->isValidDisplayName($fullNickname));
        $this->assertFalse($this->user->isValidDisplayName('DeLbOy TrOttEr'));
        $this->assertTrue($this->user->isValidDisplayName('Delboy Trotter'));
    }

    /** @test */
    public function it_stores_primary_emails_within_the_account_model()
    {
        $this->assertEquals('i_sleep@gmail.com', $this->user->email);

        $this->assertDatabaseHas('mship_account', [
            'id' => $this->user->id,
            'email' => 'i_sleep@gmail.com',
        ]);
    }

    /** @test */
    public function it_determines_if_the_given_email_exists_on_the_account()
    {
        $verified = true;
        $email = $this->user->addSecondaryEmail('i_dont_sleep@gmail.com', $verified);

        $this->assertTrue($this->user->fresh()->hasEmail($email->email));
    }

    /** @test */
    public function it_determines_if_the_given_email_exists_on_the_account_as_a_secondary_email()
    {
        $verified = true;
        $email = $this->user->addSecondaryEmail('i_dont_sleep@gmail.com', $verified);

        $checkPrimaryEmail = false;
        $this->assertTrue($this->user->fresh()->hasEmail($email->email, $checkPrimaryEmail));
    }

    /** @test */
    public function it_determines_if_the_given_email_already_exists_on_the_account_as_a_primary_email()
    {
        $this->assertTrue($this->user->fresh()->hasEmail('i_sleep@gmail.com'));
    }

    /** @test */
    public function it_doesnt_permit_storing_of_primary_email_as_secondary()
    {
        $verified = true;
        $this->user->addSecondaryEmail('i_sleep@gmail.com', $verified);

        $this->assertCount(0, $this->user->fresh()->secondaryEmails);
        $this->assertDatabaseMissing('mship_account_email', [
            'account_id' => $this->user->id,
            'email' => 'i_sleep@gmail.com',
        ]);
    }

    /** @test */
    public function it_allows_secondary_emails_to_be_stored()
    {
        $verified = false;
        $email = $this->user->addSecondaryEmail('i_also_sleep@hotmail.com', $verified);

        $this->assertCount(1, $this->user->fresh()->secondaryEmails);
        $this->assertContains($email->id, $this->user->fresh()->secondaryEmails->pluck('id'));

        $this->assertDatabaseHas('mship_account_email', [
            'account_id' => $this->user->id,
            'email' => 'i_also_sleep@hotmail.com',
        ]);

        Notification::assertSentTo($this->user, EmailVerification::class);
    }

    /** @test */
    public function it_doesnt_list_new_secondary_emails_as_verified()
    {
        $verified = false;
        $email = $this->user->addSecondaryEmail('i_too_sleep@hotmail.com', $verified);

        $this->assertCount(0, $this->user->verified_secondary_emails);
        $this->assertNotContains($email->id, $this->user->verified_secondary_emails->pluck('id'));

        Notification::assertSentTo($this->user, EmailVerification::class);
    }

    /** @test */
    public function it_lists_secondary_emails_as_verified()
    {
        $verified = true;
        $email = $this->user->addSecondaryEmail('i_three_sleep@hotmail.com', $verified);

        $this->assertContains($email->id, $this->user->fresh()->verified_secondary_emails->pluck('id'));
    }

    /** @test */
    public function it_deletes_email_from_db()
    {
        $verified = true;
        $email = $this->user->addSecondaryEmail('i_four_sleep@gmail.com', $verified);

        $this->assertContains($email->id, $this->user->fresh()->secondaryEmails->pluck('id'));

        $email->delete();

        $this->assertEquals(false, $email->exists);
        $this->assertNotContains($email->id, $this->user->fresh()->secondaryEmails->pluck('id'));
    }

    /** @test */
    public function it_upgrades_email_from_secondary_to_primary()
    {
        $verified = true;
        $email = $this->user->addSecondaryEmail('sauron@gmail.com', $verified);

        $this->assertContains($email->id, $this->user->fresh()->secondaryEmails->pluck('id'));
        $this->assertNotEquals('sauron@gmail.com', $this->user->fresh()->email);

        $this->user->fresh()->setEmail('sauron@gmail.com');

        $this->assertNotContains($email->id, $this->user->fresh()->secondaryEmails->pluck('id'));
        $this->assertEquals('sauron@gmail.com', $this->user->fresh()->email);
    }

    /** @test */
    public function it_touches_account_updated_at_when_adding_an_email()
    {
        $originalUpdatedAt = $this->user->updated_at;

        // Simulate an update one day later.
        Carbon::setTestNow(Carbon::now()->addDay());

        $verified = true;
        $email = $this->user->addSecondaryEmail('i_four_sleep@gmail.com', $verified);
        $email->save();

        $this->assertNotEquals($originalUpdatedAt, $this->user->fresh()->updated_at);
    }

    /** @test */
    public function it_stores_qualifications()
    {
        $qualification = Qualification::factory()->create();

        $this->user->addQualification($qualification);

        $this->assertTrue($this->user->fresh()->hasQualification($qualification));

        $this->assertDatabaseHas('mship_account_qualification', [
            'account_id' => $this->user->id,
            'qualification_id' => $qualification->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function it_correctly_reports_qualifications()
    {
        Carbon::setTestNow(Carbon::now()); // Check this works even when the timestamps are the same

        $mockS1Qual = Qualification::factory()->atc()->create([
            'code' => 'AS1',
            'vatsim' => 1,
        ]);
        $mockS2Qual = Qualification::factory()->atc()->create([
            'code' => 'AS2',
            'vatsim' => 2,
        ]);
        $mockP1Qual = Qualification::factory()->pilot()->create([
            'code' => 'AP1',
            'vatsim' => 3,
        ]);
        $mockP2Qual = Qualification::factory()->pilot()->create([
            'code' => 'AP2',
            'vatsim' => 4,
        ]);

        $pilotMilitary = Qualification::factory()->atc()->create([
            'code' => 'MP1',
            'vatsim' => 3,
            'type' => 'pilot_military',
        ]);

        $this->user->qualifications()->sync([$mockS1Qual->id, $mockS2Qual->id, $mockP1Qual->id, $mockP2Qual->id, $pilotMilitary->id]);
        $this->user = $this->user->fresh();

        $this->assertEquals($this->user->qualification_atc->id, $mockS2Qual->id);
        $this->assertEquals($this->user->qualification_pilot->id, $mockP2Qual->id);
        $this->assertEqualsCanonicalizing([$mockP1Qual->id, $mockP2Qual->id], $this->user->qualifications_pilot->map(function ($qual) {
            return $qual->id;
        })->values()->toArray());
        $this->assertEqualsCanonicalizing([$mockS2Qual->id, $mockP2Qual->id, $pilotMilitary->id], $this->user->active_qualifications->map(function ($qual) {
            return $qual->id;
        })->values()->toArray());

        Carbon::setTestNow();
    }

    /** @test */
    public function it_touches_account_updated_at_when_adding_a_qualification()
    {
        $originalUpdatedAt = $this->user->updated_at;

        // Simulate an update one day later.
        Carbon::setTestNow(Carbon::now()->addDay());

        $qualification = Qualification::factory()->create();
        $this->user->fresh()->addQualification($qualification);

        $this->assertNotEquals($originalUpdatedAt, $this->user->fresh()->updated_at);
    }

    /** @test */
    public function it_determines_that_password_is_not_set()
    {
        $this->assertFalse($this->user->hasPassword());

        $this->assertDatabaseHas('mship_account', [
            'id' => $this->user->id,
            'password' => null,
            'password_set_at' => null,
            'password_expires_at' => null,
        ]);
    }

    /** @test */
    public function it_stores_a_hashed_password()
    {
        $this->mockAuth();
        $this->user->setPassword('testing123');

        $this->user = $this->user->fresh();

        $this->assertTrue(\Hash::check('testing123', $this->user->password));

        $this->assertDatabaseHas('mship_account', [
            'id' => $this->user->id,
            'password' => $this->user->password,
        ]);

        $this->assertDatabaseMissing('mship_account', [
            'id' => $this->user->id,
            'password_set_at' => null,
            'password_expires_at' => null,
        ]);
    }

    protected function mockAuth()
    {
        \Auth::shouldReceive('user')->andReturn($this->user);
        \Auth::shouldReceive('check')->andReturn(true);
        \Auth::shouldReceive('id')->andReturn($this->user->id);
    }

    /** @test */
    public function it_determines_that_password_is_set()
    {
        $this->mockAuth();
        $this->user->setPassword('testing456');

        $this->user = $this->user->fresh();

        $this->assertTrue($this->user->hasPassword());

        $this->assertDatabaseHas('mship_account', [
            'id' => $this->user->id,
            'password' => $this->user->password,
        ]);

        $this->assertDatabaseMissing('mship_account', [
            'id' => $this->user->id,
            'password_set_at' => null,
            'password_expires_at' => null,
        ]);
    }

    /** @test */
    public function it_determines_that_password_has_expired()
    {
        $temporary = true;
        $this->mockAuth();
        $this->user->setPassword('testing911', $temporary);

        $this->user = $this->user->fresh();

        $this->assertTrue($this->user->hasPasswordExpired());
    }

    /** @test */
    public function it_overwrites_old_password_and_modifies_the_timestamps()
    {
        $this->mockAuth();
        $this->user->setPassword('testing123');

        $oldPassword = $this->user->password;
        $oldPasswordSetAt = $this->user->password_set_at;
        $oldPasswordExpiresAt = $this->user->password_expires_at;

        $this->user = $this->user->fresh();

        $this->assertDatabaseHas('mship_account', [
            'id' => $this->user->id,
            'password' => $oldPassword,
            'password_set_at' => $oldPasswordSetAt,
            'password_expires_at' => $oldPasswordExpiresAt,
        ]);

        $this->user->setPassword('testing456');

        $this->assertDatabaseMissing('mship_account', [
            'id' => $this->user->id,
            'password' => $oldPassword,
            'password_set_at' => $oldPasswordSetAt,
            'password_expires_at' => $oldPasswordExpiresAt,
        ]);
    }

    /** @test */
    public function it_adds_role_to_account()
    {
        $role = factory(Role::class)->create();

        $this->user->fresh()->assignRole($role);

        $this->assertTrue($this->user->fresh()->roles->contains($role->id));

        $this->assertDatabaseHas('mship_account_role', [
            'model_id' => $this->user->id,
            'role_id' => $role->id,
        ]);
    }

    /** @test */
    public function it_determines_if_the_account_has_a_given_role()
    {
        $role = factory(Role::class)->create();

        $this->user->fresh()->roles()->attach($role);

        $this->assertTrue($this->user->fresh()->hasRole($role));
    }

    /** @test */
    public function it_removes_role_from_account()
    {
        $role = factory(Role::class)->create();

        $this->user->fresh()->assignRole($role);

        $this->assertTrue($this->user->fresh()->roles->contains($role->id));
        $this->assertDatabaseHas('mship_account_role', [
            'model_id' => $this->user->id,
            'role_id' => $role->id,
        ]);

        $this->user->fresh()->removeRole($role);

        $this->assertFalse($this->user->fresh()->roles->contains($role->id));
        $this->assertDatabaseMissing('mship_account_role', [
            'model_id' => $this->user->id,
            'role_id' => $role->id,
        ]);
    }

    /** @test */
    public function it_determines_that_password_is_not_mandatory()
    {
        $this->assertFalse($this->user->mandatory_password);
    }

    /** @test */
    public function it_determines_that_password_is_mandatory()
    {
        $role = factory(Role::class)->create(['password_mandatory' => true]);

        $this->user->assignRole($role);

        $this->user = $this->user->fresh();

        $this->assertTrue($this->user->mandatory_password);
    }

    /** @test */
    public function it_returns_an_infinite_session_timeout()
    {
        $roleWithInfiniteTimeout = factory(Role::class)->create([
            'session_timeout' => 0,
        ]);

        $this->user->roles()->attach($roleWithInfiniteTimeout);

        $this->assertEquals(0, $this->user->fresh()->session_timeout);
    }

    /** @test */
    public function it_returns_a_non_infinite_session_timeout()
    {
        $roleWithInfiniteTimeout = factory(Role::class)->create([
            'session_timeout' => 0,
        ]);

        $roleWithNonInfiniteTimeout = factory(Role::class)->create([
            'session_timeout' => 10,
        ]);

        $this->user->roles()->attach($roleWithInfiniteTimeout);
        $this->user->roles()->attach($roleWithNonInfiniteTimeout);

        $this->assertEquals(10, $this->user->fresh()->session_timeout);
    }

    /** @test */
    public function it_correctly_reports_fully_defined()
    {
        $this->assertTrue($this->user->fully_defined);

        $this->user->email = null;
        $this->user->save();

        $this->assertFalse($this->user->fully_defined);
    }

    /** @test */
    public function it_correctly_reports_fully_defined_with_no_atc_qualification()
    {
        $this->user->qualifications()->sync([]);
        $this->assertFalse($this->user->fresh()->fully_defined);

        $this->user->updateVatsimRatings(1, 1);
        $this->assertTrue($this->user->fresh()->fully_defined);
    }

    /** @test */
    public function it_sets_and_returns_a_users_active_status()
    {
        $this->assertFalse($this->user->is_inactive);

        $this->user->is_inactive = true;
        $this->user->save();

        $this->assertTrue($this->user->is_inactive);
    }

    /** @test */
    public function it_detects_on_roster()
    {
        $account = Account::factory()->create();
        $divisionState = State::findByCode('DIVISION')->firstOrFail();
        $account->addState($divisionState, 'EUR', 'GBR');
        $r1 = Roster::create(['account_id' => $account->id])->save();
        $r = Roster::firstOrFail();
        $account->refresh();

        $this->assertTrue($account->onRoster());
    }

    /** @test */
    public function it_detects_not_on_roster()
    {
        $this->assertFalse($this->user->onRoster());
    }
}
