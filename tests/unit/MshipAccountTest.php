<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

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
        $requireEmailID = true;
        $emailID = $this->account->addSecondaryEmail("i_sleep@gmail.com", $verified, $requireEmailID);

        $this->assertCount(0, $this->account->secondaryEmails());
        $this->assertNotContains($emailID, $this->account->secondary_emails);
    }

    /** @test */
    public function it_allows_secondary_emails_to_be_stored(){
        $this->expectsJobs(\App\Jobs\Mship\Email\TriggerNewEmailVerificationProcess::class);

        $verified = false;
        $requireEmailID = true;
        $emailID = $this->account->addSecondaryEmail("i_also_sleep@hotmail.com", $verified, $requireEmailID);

        $this->assertCount(1, $this->account->fresh()->secondaryEmails);
        $this->assertContains($emailID, $this->account->fresh()->secondaryEmails->pluck("id"));
    }

    /** @test */
    public function it_doesnt_list_new_secondary_emails_as_verified(){
        $this->expectsJobs(\App\Jobs\Mship\Email\TriggerNewEmailVerificationProcess::class);

        $verified = false;
        $requireEmailID = true;
        $emailID = $this->account->addSecondaryEmail("i_too_sleep@hotmail.com", $verified, $requireEmailID);

        $this->assertCount(0, $this->account->verified_secondary_emails);
        $this->assertNotContains($emailID, $this->account->verified_secondary_emails);
    }

    /** @test */
    public function it_lists_secondary_emails_as_verified(){
        $this->expectsJobs(\App\Jobs\Mship\Email\TriggerNewEmailVerificationProcess::class);

        $verified = true;
        $requireEmailID = true;
        $emailID = $this->account->addSecondaryEmail("i_three_sleep@hotmail.com", $verified, $requireEmailID);

        $this->assertCount(1, $this->account->verified_secondary_emails);
        $this->assertNotContains($emailID, $this->account->verified_secondary_emails);
    }
}
