<?php

namespace Tests\Unit\Discord;

use App\Models\Discord\DiscordQualificationRole;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DiscordQualificationRoleTest extends TestCase
{
    use DatabaseTransactions;

    private $homeAccount, $internationalAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->homeAccount = factory(Account::class)->states('withQualification')->create();
        $this->homeAccount->addState(\App\Models\Mship\State::findByCode('DIVISION'));
        $this->homeAccount = $this->homeAccount->fresh();

        $this->internationalAccount = factory(Account::class)->create();
        $this->internationalAccount->addQualification($this->homeAccount->qualifications->first());
        $this->internationalAccount->addQualification($this->homeAccount->qualifications->last());
        $this->internationalAccount->addState(\App\Models\Mship\State::findByCode('INTERNATIONAL'));
        $this->internationalAccount = $this->internationalAccount->fresh();
    }

    /** @test */
    public function itReportsIfAccountSatifiesWithoutState()
    {

        $role = DiscordQualificationRole::factory()->create(['qualification_id' => $this->homeAccount->qualifications->first()->id, 'state_id' => null]);

        $this->assertTrue($role->accountSatisfies($this->homeAccount));
        $this->assertTrue($role->accountSatisfies($this->internationalAccount));

        $this->assertFalse($role->accountSatisfies($this->user));
    }

    /** @test */
    public function itReportsIfAccountSatifiesWithState()
    {
        $role = DiscordQualificationRole::factory()->create(['qualification_id' => $this->homeAccount->qualifications->first()->id, 'state_id' => \App\Models\Mship\State::findByCode('DIVISION')->id]);

        $this->assertTrue($role->accountSatisfies($this->homeAccount));

        $this->assertFalse($role->accountSatisfies($this->internationalAccount));
        $this->assertFalse($role->accountSatisfies($this->user));
    }
}
