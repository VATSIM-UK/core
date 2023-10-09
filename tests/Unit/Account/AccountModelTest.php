<?php

namespace Tests\Unit\Account;

use App\Models\Mship\Qualification;
use App\Notifications\Mship\EmailVerification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccountModelTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
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
    public function itStoresBasicMemberData()
    {
        $this->assertDatabaseHas('mship_account', [
            'name_first' => $this->user->name_first,
            'name_last' => $this->user->name_last,
            'email' => $this->user->email,
        ]);

        $this->assertTrue($this->user->exists);
    }

    /** @test */
    public function itCorrectlyFormatsNames()
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
    public function itCorrectlyDeterminesIfTheMemberNameIsAValidDisplayName()
    {
        $this->assertTrue($this->user->isValidDisplayName($this->user->real_name));
    }

    /** @test */
    public function itCorrectlyDeterminesIfTheNicknameIsAValidDisplayName()
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
    public function itAllowsValidIncrementedNicknames()
    {
        $this->assertTrue($this->user->isDuplicateDisplayName("{$this->user->name_first} {$this->user->name_last}1"));
        $this->assertTrue($this->user->isDuplicateDisplayName("{$this->user->name_first} {$this->user->name_last}5"));
        $this->assertFalse($this->user->isDuplicateDisplayName("Joe {$this->user->name_last}5"));
    }

    /** @test */
    public function itDeterminesThatANameIsStillValidEvenWithANicknameSet()
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
    public function itDeterminesWhenThereIsAnInvalidDisplayName()
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
    public function itStoresPrimaryEmailsWithinTheAccountModel()
    {
        $this->assertEquals('i_sleep@gmail.com', $this->user->email);

        $this->assertDatabaseHas('mship_account', [
            'id' => $this->user->id,
            'email' => 'i_sleep@gmail.com',
        ]);
    }

    /** @test */
    public function itDeterminesIfTheGivenEmailExistsOnTheAccount()
    {
        $verified = true;
        $email = $this->user->addSecondaryEmail('i_dont_sleep@gmail.com', $verified);

        $this->assertTrue($this->user->fresh()->hasEmail($email->email));
    }

    /** @test */
    public function itDeterminesIfTheGivenEmailExistsOnTheAccountAsASecondaryEmail()
    {
        $verified = true;
        $email = $this->user->addSecondaryEmail('i_dont_sleep@gmail.com', $verified);

        $checkPrimaryEmail = false;
        $this->assertTrue($this->user->fresh()->hasEmail($email->email, $checkPrimaryEmail));
    }

    /** @test */
    public function itDeterminesIfTheGivenEmailAlreadyExistsOnTheAccountAsAPrimaryEmail()
    {
        $this->assertTrue($this->user->fresh()->hasEmail('i_sleep@gmail.com'));
    }

    /** @test */
    public function itDoesntPermitStoringOfPrimaryEmailAsSecondary()
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
    public function itAllowsSecondaryEmailsToBeStored()
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
    public function itDoesntListNewSecondaryEmailsAsVerified()
    {
        $verified = false;
        $email = $this->user->addSecondaryEmail('i_too_sleep@hotmail.com', $verified);

        $this->assertCount(0, $this->user->verified_secondary_emails);
        $this->assertNotContains($email->id, $this->user->verified_secondary_emails->pluck('id'));

        Notification::assertSentTo($this->user, EmailVerification::class);
    }

    /** @test */
    public function itListsSecondaryEmailsAsVerified()
    {
        $verified = true;
        $email = $this->user->addSecondaryEmail('i_three_sleep@hotmail.com', $verified);

        $this->assertContains($email->id, $this->user->fresh()->verified_secondary_emails->pluck('id'));
    }

    /** @test */
    public function itDeletesEmailFromDb()
    {
        $verified = true;
        $email = $this->user->addSecondaryEmail('i_four_sleep@gmail.com', $verified);

        $this->assertContains($email->id, $this->user->fresh()->secondaryEmails->pluck('id'));

        $email->delete();

        $this->assertEquals(false, $email->exists);
        $this->assertNotContains($email->id, $this->user->fresh()->secondaryEmails->pluck('id'));
    }

    /** @test */
    public function itUpgradesEmailFromSecondaryToPrimary()
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
    public function itTouchesAccountUpdatedAtWhenAddingAnEmail()
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
    public function itStoresQualifications()
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
    public function itCorrectlyReportsQualifications()
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
        })->all());
        $this->assertEqualsCanonicalizing([$mockS2Qual->id, $mockP2Qual->id, $pilotMilitary->id], $this->user->active_qualifications->map(function ($qual) {
            return $qual->id;
        })->all());

        Carbon::setTestNow();
    }

    /** @test */
    public function itTouchesAccountUpdatedAtWhenAddingAQualification()
    {
        $originalUpdatedAt = $this->user->updated_at;

        // Simulate an update one day later.
        Carbon::setTestNow(Carbon::now()->addDay());

        $qualification = Qualification::factory()->create();
        $this->user->fresh()->addQualification($qualification);

        $this->assertNotEquals($originalUpdatedAt, $this->user->fresh()->updated_at);
    }

    /** @test */
    public function itDeterminesThatPasswordIsNotSet()
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
    public function itStoresAHashedPassword()
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
    public function itDeterminesThatPasswordIsSet()
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
    public function itDeterminesThatPasswordHasExpired()
    {
        $temporary = true;
        $this->mockAuth();
        $this->user->setPassword('testing911', $temporary);

        $this->user = $this->user->fresh();

        $this->assertTrue($this->user->hasPasswordExpired());
    }

    /** @test */
    public function itOverwritesOldPasswordAndModifiesTheTimestamps()
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
    public function itAddsRoleToAccount()
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
    public function itDeterminesIfTheAccountHasAGivenRole()
    {
        $role = factory(Role::class)->create();

        $this->user->fresh()->roles()->attach($role);

        $this->assertTrue($this->user->fresh()->hasRole($role));
    }

    /** @test */
    public function itRemovesRoleFromAccount()
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
    public function itDeterminesThatPasswordIsNotMandatory()
    {
        $this->assertFalse($this->user->mandatory_password);
    }

    /** @test */
    public function itDeterminesThatPasswordIsMandatory()
    {
        $role = factory(Role::class)->create(['password_mandatory' => true]);

        $this->user->assignRole($role);

        $this->user = $this->user->fresh();

        $this->assertTrue($this->user->mandatory_password);
    }

    /** @test */
    public function itReturnsAnInfiniteSessionTimeout()
    {
        $roleWithInfiniteTimeout = factory(Role::class)->create([
            'session_timeout' => 0,
        ]);

        $this->user->roles()->attach($roleWithInfiniteTimeout);

        $this->assertEquals(0, $this->user->fresh()->session_timeout);
    }

    /** @test */
    public function itReturnsANonInfiniteSessionTimeout()
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
    public function itCorrectlyReportsFullyDefined()
    {
        $this->assertTrue($this->user->fully_defined);

        $this->user->email = null;
        $this->user->save();

        $this->assertFalse($this->user->fully_defined);
    }

    /** @test */
    public function itCorrectlyReportsFullyDefinedWithNoATCQualification()
    {
        $this->user->qualifications()->sync([]);
        $this->assertFalse($this->user->fresh()->fully_defined);

        $this->user->updateVatsimRatings(1, 1);
        $this->assertTrue($this->user->fresh()->fully_defined);
    }

    /** @test */
    public function itSetsAndReturnsAUsersActiveStatus()
    {
        $this->assertFalse($this->user->is_inactive);

        $this->user->is_inactive = true;
        $this->user->save();

        $this->assertTrue($this->user->is_inactive);
    }
}
