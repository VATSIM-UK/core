<?php

class MshipAccountTest extends TestCase
{
    private $account;

    public function setUp(){
        parent::setUp();

        $this->account = factory(App\Models\Mship\Account::class)->create([
            "name_first" => "John",
            "name_last" => "Doe",
            "email" => "i_sleep@gmail.com"
        ]);
    }

    /** @test **/
    public function it_stores_basic_member_data()
    {
        $this->seeInDatabase("mship_account", [
            "name_first" => "John",
            "name_last" => "Doe",
            "email" => "i_sleep@gmail.com",
        ]);

        $this->assertTrue($this->account->exists);
    }

    /** @test **/
    public function it_correctly_formats_names()
    {
        $member = factory(\App\Models\Mship\Account::class)->create([
            "name_first" => "mary",
            "name_last" => "JANE",
        ]);

        $this->assertEquals("Mary", $member->name_first);
        $this->assertEquals("Jane", $member->name_last);
    }

    /** @test */
    public function it_stores_primary_emails_within_the_account_model(){
        $this->assertEquals("i_sleep@gmail.com", $this->account->email);
    }

    /** @test */
    public function it_doesnt_permit_storing_of_primary_email_as_secondary(){
        $this->setExpectedException(App\Exceptions\Mship\DuplicateEmailException::class);

        $verified = true;
        $email = $this->account->addSecondaryEmail("i_sleep@gmail.com", $verified);

        $this->assertCount(0, $this->account->fresh()->secondaryEmails);
        $this->assertNotContains($email->id, $this->account->fresh()->secondaryEmails->pluck("id"));
    }

    /** @test */
    public function it_doesnt_permit_duplicate_secondary_emails_on_same_model(){
        $this->setExpectedException(App\Exceptions\Mship\DuplicateEmailException::class);

        $verified = true;
        $this->account->addSecondaryEmail("test_email@gmail.com", $verified);
        $this->account->fresh()->addSecondaryEmail("test_email@gmail.com", $verified);
    }

    /** @test */
    public function it_allows_secondary_emails_to_be_stored(){
        $this->expectsJobs(\App\Jobs\Mship\Email\TriggerNewEmailVerificationProcess::class);

        $verified = false;
        $email = $this->account->addSecondaryEmail("i_also_sleep@hotmail.com", $verified);

        $this->assertCount(1, $this->account->fresh()->secondaryEmails);
        $this->assertContains($email->id, $this->account->fresh()->secondaryEmails->pluck("id"));
    }

    /** @test */
    public function it_doesnt_list_new_secondary_emails_as_verified(){
        $this->expectsJobs(\App\Jobs\Mship\Email\TriggerNewEmailVerificationProcess::class);

        $verified = false;
        $email = $this->account->addSecondaryEmail("i_too_sleep@hotmail.com", $verified);

        $this->assertCount(0, $this->account->verified_secondary_emails);
        $this->assertNotContains($email->id, $this->account->verified_secondary_emails->pluck("id"));
    }

    /** @test */
    public function it_lists_secondary_emails_as_verified(){
        $this->doesntExpectJobs(\App\Jobs\Mship\Email\TriggerNewEmailVerificationProcess::class);

        $verified = true;
        $email = $this->account->addSecondaryEmail("i_three_sleep@hotmail.com", $verified);

        $this->assertContains($email->id, $this->account->fresh()->verified_secondary_emails->pluck("id"));
    }

    /** @test */
    public function it_deletes_email_from_db(){
        $verified = true;
        $email = $this->account->addSecondaryEmail("i_four_sleep@gmail.com", $verified);

        $this->assertContains($email->id, $this->account->fresh()->secondaryEmails->pluck("id"));

        $email->delete();

        $this->assertEquals(false, $email->exists);
        $this->assertNotContains($email->id, $this->account->fresh()->secondaryEmails->pluck("id"));
    }

    /** @test */
    public function it_touches_account_updated_at_when_adding_an_email(){
        $originalUpdatedAt = $this->account->updated_at;

        sleep(1);

        $verified = true;
        $email = $this->account->addSecondaryEmail("i_four_sleep@gmail.com", $verified);
        $email->save();

        $this->assertNotEquals($originalUpdatedAt, $this->account->fresh()->updated_at);
    }

    /** @test **/
    public function it_correctly_stores_qualifications()
    {
        $qualification = \App\Models\Mship\Qualification::first();

        $this->account->addQualification($qualification);

        $this->assertTrue($this->account->fresh()->hasQualification($qualification));

        $this->seeInDatabase("mship_account_qualification", [
            "account_id" => $this->account->id,
            "qualification_id" => $qualification->qualification_id,
            "deleted_at" => null,
        ]);
    }

    /** @test **/
    public function it_returns_duplicate_qualification_error_when_adding_qualification()
    {
        $this->setExpectedException(\App\Exceptions\Mship\DuplicateQualificationException::class);

        $qualificationOne = \App\Models\Mship\Qualification::first();
        $qualificationTwo = \App\Models\Mship\Qualification::first();

        $this->account->addQualification($qualificationOne);
        $this->account->fresh()->addQualification($qualificationTwo);
    }
}