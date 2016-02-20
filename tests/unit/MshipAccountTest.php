<?php

class MshipAccountTest extends TestCase
{
    private $account;

    public function setUp(){
        parent::setUp();
        $this->account = factory(App\Models\Mship\Account::class)->create(["email" => "i_sleep@gmail.com"]);
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
        $emailID = $this->account->addSecondaryEmail("i_three_sleep@hotmail.com", $verified);

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
        $originalUpdatedAt = $this->account->updated_at->toDateTimeString();

        $verified = true;
        $email = $this->account->addSecondaryEmail("i_four_sleep@gmail.com", $verified);

        $this->assertNotEquals($originalUpdatedAt, $email->account->fresh()->updated_at->toDateTimeString());
    }
}
