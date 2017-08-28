<?php

namespace Tests\Unit;

use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Mship\Role;
use App\Notifications\Mship\EmailVerification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\BrowserKitTestCase;
use Tests\TestCase;

/**
 * Class MshipAccountTest
 * @package Tests\Unit
 */
class MshipAccountTest extends TestCase
{
    use DatabaseTransactions;

    /** @var Account $account */
    private $account;

    public function setUp()
    {
        parent::setUp();

        $this->account = factory(Account::class)->create([
            "name_first" => "John",
            "name_last" => "Doe",
            "email" => "i_sleep@gmail.com",
        ]);
    }

    protected function mockAuth()
    {
        \Auth::shouldReceive('user')->andReturn($this->account);
        \Auth::shouldReceive('check')->andReturn(true);
        \Auth::shouldReceive('id')->andReturn($this->account->id);
    }

    /** @test */
    public function itStoresBasicMemberData()
    {
        $this->assertDatabaseHas("mship_account", [
            "name_first" => "John",
            "name_last" => "Doe",
            "email" => "i_sleep@gmail.com",
        ]);

        $this->assertTrue($this->account->exists);
    }

    /** @test */
    public function itCorrectlyFormatsNames()
    {
        $member = factory(Account::class)->create([
            "name_first" => "mary",
            "name_last" => "JANE",
        ]);

        $this->assertEquals("Mary", $member->name_first);
        $this->assertEquals("Jane", $member->name_last);

        $this->assertDatabaseHas("mship_account", [
            "id" => $member->id,
            "name_first" => "Mary",
            "name_last" => "Jane",
        ]);
    }

    /** @test */
    public function itCorrectlyDeterminesIfTheMemberNameIsAValidDisplayName()
    {
        $this->assertTrue($this->account->isValidDisplayName($this->account->real_name));
    }

    /** @test */
    public function itCorrectlyDeterminesIfTheNicknameIsAValidDisplayName()
    {
        $this->account->nickname = "Delboy";
        $this->account->save();

        $this->assertDatabaseHas("mship_account", [
            "id" => $this->account->id,
            "nickname" => "Delboy",
        ]);

        $fullNickname = "Delboy " . $this->account->name_last;
        $this->assertTrue($this->account->isValidDisplayName($fullNickname));
    }

    /** @test * */
    public function itDeterminesThatANameIsStillValidEvenWithANickanemSet()
    {
        $this->account->nickname = "Delboy";
        $this->account->save();

        $this->assertDatabaseHas("mship_account", [
            "id" => $this->account->id,
            "nickname" => "Delboy",
        ]);

        $this->assertTrue($this->account->isValidDisplayName($this->account->real_name));
    }

    /** @test */
    public function itDeterminesWhenThereIsAnInvalidDisplayName()
    {
        $this->account->nickname = "Delboy";
        $this->account->save();

        $this->assertDatabaseHas("mship_account", [
            "id" => $this->account->id,
            "nickname" => "Delboy",
        ]);

        $fullNickname = "Rodney " . $this->account->name_last;
        $this->assertFalse($this->account->isValidDisplayName($fullNickname));
    }

    /** @test */
    public function itStoresPrimaryEmailsWithinTheAccountModel()
    {
        $this->assertEquals("i_sleep@gmail.com", $this->account->email);

        $this->assertDatabaseHas("mship_account", [
            "id" => $this->account->id,
            "email" => "i_sleep@gmail.com",
        ]);
    }

    /** @test * */
    public function itDeterminesIfTheGivenEmailExistsOnTheAccount()
    {
        $verified = true;
        $email = $this->account->addSecondaryEmail("i_dont_sleep@gmail.com", $verified);

        $this->assertTrue($this->account->fresh()->hasEmail($email->email));
    }

    /** @test * */
    public function itDeterminesIfTheGivenEmailExistsOnTheAccountAsASecondaryEmail()
    {
        $verified = true;
        $email = $this->account->addSecondaryEmail("i_dont_sleep@gmail.com", $verified);

        $checkPrimaryEmail = false;
        $this->assertTrue($this->account->fresh()->hasEmail($email->email, $checkPrimaryEmail));
    }

    /** @test * */
    public function itDeterminesIfTheGivenEmailAlreadyExistsOnTheAccountAsAPrimaryEmail()
    {
        $this->assertTrue($this->account->fresh()->hasEmail("i_sleep@gmail.com"));
    }

    /** @test */
    public function itDoesntPermitStoringOfPrimaryEmailAsSecondary()
    {
        $verified = true;
        $this->account->addSecondaryEmail("i_sleep@gmail.com", $verified);

        $this->assertCount(0, $this->account->fresh()->secondaryEmails);
        $this->assertDatabaseMissing("mship_account_email", [
            "account_id" => $this->account->id,
            "email" => "i_sleep@gmail.com",
        ]);
    }

    /** @test */
    public function itAllowsSecondaryEmailsToBeStored()
    {
        $this->expectsNotification($this->account, EmailVerification::class);

        $verified = false;
        $email = $this->account->addSecondaryEmail("i_also_sleep@hotmail.com", $verified);

        $this->assertCount(1, $this->account->fresh()->secondaryEmails);
        $this->assertContains($email->id, $this->account->fresh()->secondaryEmails->pluck("id"));

        $this->assertDatabaseHas("mship_account_email", [
            "account_id" => $this->account->id,
            "email" => "i_also_sleep@hotmail.com",
        ]);
    }

    /** @test */
    public function itDoesntListNewSecondaryEmailsAsVerified()
    {
        $this->expectsNotification($this->account, EmailVerification::class);

        $verified = false;
        $email = $this->account->addSecondaryEmail("i_too_sleep@hotmail.com", $verified);

        $this->assertCount(0, $this->account->verified_secondary_emails);
        $this->assertNotContains($email->id, $this->account->verified_secondary_emails->pluck("id"));
    }

    /** @test */
    public function itListsSecondaryEmailsAsVerified()
    {
        $verified = true;
        $email = $this->account->addSecondaryEmail("i_three_sleep@hotmail.com", $verified);

        $this->assertContains($email->id, $this->account->fresh()->verified_secondary_emails->pluck("id"));
    }

    /** @test */
    public function itDeletesEmailFromDb()
    {
        $verified = true;
        $email = $this->account->addSecondaryEmail("i_four_sleep@gmail.com", $verified);

        $this->assertContains($email->id, $this->account->fresh()->secondaryEmails->pluck("id"));

        $email->delete();

        $this->assertEquals(false, $email->exists);
        $this->assertNotContains($email->id, $this->account->fresh()->secondaryEmails->pluck("id"));
    }

    /** @test * */
    public function itUpgradesEmailFromSecondaryToPrimary()
    {
        $verified = true;
        $email = $this->account->addSecondaryEmail("sauron@gmail.com", $verified);

        $this->assertContains($email->id, $this->account->fresh()->secondaryEmails->pluck("id"));
        $this->assertNotEquals("sauron@gmail.com", $this->account->fresh()->email);

        $this->account->fresh()->setEmail("sauron@gmail.com");

        $this->assertNotContains($email->id, $this->account->fresh()->secondaryEmails->pluck("id"));
        $this->assertEquals("sauron@gmail.com", $this->account->fresh()->email);
    }

    /** @test */
    public function itTouchesAccountUpdatedAtWhenAddingAnEmail()
    {
        $originalUpdatedAt = $this->account->updated_at;

        sleep(1);

        $verified = true;
        $email = $this->account->addSecondaryEmail("i_four_sleep@gmail.com", $verified);
        $email->save();

        $this->assertNotEquals($originalUpdatedAt, $this->account->fresh()->updated_at);
    }

    /** @test */
    public function itStoresQualifications()
    {
        $qualification = factory(Qualification::class)->create();

        $this->account->addQualification($qualification);

        $this->assertTrue($this->account->fresh()->hasQualification($qualification));

        $this->assertDatabaseHas("mship_account_qualification", [
            "account_id" => $this->account->id,
            "qualification_id" => $qualification->id,
            "deleted_at" => null,
        ]);
    }

    /** @test */
    public function itTouchesAccountUpdatedAtWhenAddingAQualification()
    {
        $originalUpdatedAt = $this->account->updated_at;

        sleep(1);

        $qualification = factory(Qualification::class)->create();
        $this->account->fresh()->addQualification($qualification);

        $this->assertNotEquals($originalUpdatedAt, $this->account->fresh()->updated_at);
    }

    /** @test */
    public function itReturnsTheCorrectAccountBasedOnSlackId()
    {
        $slackID = substr(strrev(uniqid()), 0, 10);

        $this->account->slack_id = $slackID;
        $this->account->save();


        $slackAccount = Account::where('slack_id', $slackID)->first();

        $this->assertEquals($slackAccount->id, $this->account->fresh()->id);
    }

    /** @test * */
    public function itDeterminesThatPasswordIsNotSet()
    {
        $this->assertFalse($this->account->hasPassword());

        $this->assertDatabaseHas("mship_account", [
            "id" => $this->account->id,
            "password" => null,
            "password_set_at" => null,
            "password_expires_at" => null,
        ]);
    }

    /** @test * */
    public function itStoresAHashedPassword()
    {
        $this->mockAuth();
        $this->account->setPassword("testing123");

        $this->account = $this->account->fresh();

        $this->assertTrue(\Hash::check("testing123", $this->account->password));

        $this->assertDatabaseHas("mship_account", [
            "id" => $this->account->id,
            "password" => $this->account->password,
        ]);

        $this->assertDatabaseMissing("mship_account", [
            "id" => $this->account->id,
            "password_set_at" => null,
            "password_expires_at" => null,
        ]);
    }

    /** @test * */
    public function itDeterminesThatPasswordIsSet()
    {
        $this->mockAuth();
        $this->account->setPassword("testing456");

        $this->account = $this->account->fresh();

        $this->assertTrue($this->account->hasPassword());

        $this->assertDatabaseHas("mship_account", [
            "id" => $this->account->id,
            "password" => $this->account->password,
        ]);

        $this->assertDatabaseMissing("mship_account", [
            "id" => $this->account->id,
            "password_set_at" => null,
            "password_expires_at" => null,
        ]);
    }

    /** @test * */
    public function itDeterminesThatPasswordHasExpired()
    {
        $temporary = true;
        $this->mockAuth();
        $this->account->setPassword("testing911", $temporary);

        sleep(1); // Necessary to check the password has expired.

        $this->account = $this->account->fresh();

        $this->assertTrue($this->account->hasPasswordExpired());
    }

    /** @test * */
    public function itOverwritesOldPasswordAndModifiesTheTimestamps()
    {
        $this->mockAuth();
        $this->account->setPassword("testing123");

        $oldPassword = $this->account->password;
        $oldPasswordSetAt = $this->account->password_set_at;
        $oldPasswordExpiresAt = $this->account->password_expires_at;

        $this->account = $this->account->fresh();

        $this->assertDatabaseHas("mship_account", [
            "id" => $this->account->id,
            "password" => $oldPassword,
            "password_set_at" => $oldPasswordSetAt,
            "password_expires_at" => $oldPasswordExpiresAt,
        ]);

        $this->account->setPassword("testing456");

        $this->assertDatabaseMissing("mship_account", [
            "id" => $this->account->id,
            "password" => $oldPassword,
            "password_set_at" => $oldPasswordSetAt,
            "password_expires_at" => $oldPasswordExpiresAt,
        ]);
    }

    /** @test * */
    public function itAddsRoleToAccount()
    {
        $role = factory(Role::class)->create();

        $this->account->fresh()->roles()->attach($role);

        $this->assertTrue($this->account->fresh()->roles->contains($role->id));

        $this->assertDatabaseHas("mship_account_role", [
            "account_id" => $this->account->id,
            "role_id" => $role->id,
        ]);
    }

    /** @test * */
    public function itDeterminesIfTheAccountHasAGivenRole()
    {
        $role = factory(Role::class)->create();

        $this->account->fresh()->roles()->attach($role);

        $this->assertTrue($this->account->fresh()->hasRole($role));
    }

    /** @test * */
    public function itRemovesRoleFromAccount()
    {
        $role = factory(Role::class)->create();

        $this->account->fresh()->roles()->attach($role);

        $this->assertTrue($this->account->fresh()->roles->contains($role->id));
        $this->assertDatabaseHas("mship_account_role", [
            "account_id" => $this->account->id,
            "role_id" => $role->id,
        ]);

        $this->account->fresh()->removeRole($role);

        $this->assertFalse($this->account->fresh()->roles->contains($role->id));
        $this->assertDatabaseMissing("mship_account_role", [
            "account_id" => $this->account->id,
            "role_id" => $role->id,
        ]);
    }

    /** @test * */
    public function itDeterminesThatPasswordIsNotMandatory()
    {
        $this->assertFalse($this->account->mandatory_password);
    }

    /** @test * */
    public function itDeterminesThatPasswordIsMandatory()
    {
        $role = factory(Role::class)->create(["password_mandatory" => true]);

        $this->account->roles()->attach($role);

        $this->account = $this->account->fresh();

        $this->assertTrue($this->account->mandatory_password);
    }

    /** @test * */
    public function itReturnsAnInfiniteSessionTimeout()
    {
        $roleWithInfiniteTimeout = factory(Role::class)->create([
            "session_timeout" => 0
        ]);

        $this->account->roles()->attach($roleWithInfiniteTimeout);

        $this->assertEquals(0, $this->account->fresh()->session_timeout);
    }

    /** @test * */
    public function itReturnsANonInfiniteSessionTimeout()
    {
        $roleWithInfiniteTimeout = factory(Role::class)->create([
            "session_timeout" => 0
        ]);

        $roleWithNonInfiniteTimeout = factory(Role::class)->create([
            "session_timeout" => 10
        ]);

        $this->account->roles()->attach($roleWithInfiniteTimeout);
        $this->account->roles()->attach($roleWithNonInfiniteTimeout);

        $this->assertEquals(10, $this->account->fresh()->session_timeout);
    }

    /** @test * */
    public function itSetsAUsersActiveStatus()
    {

    }

    /** @test * */
    public function itReturnsAUsersActiveStatus()
    {

    }

    /** @test * */
    public function itSetsAUsersInactiveStatus()
    {

    }

    /** @test * */
    public function itReturnsAUsersInactiveStatus()
    {

    }

    /** @test * */
    public function itSetsAUsersLockedStatus()
    {

    }

    /** @test * */
    public function itReturnsAUsersLockedStatus()
    {

    }
}
