<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class MshipStateTest extends TestCase
{
    use DatabaseTransactions;

    private $account;

    public function setUp()
    {
        parent::setUp();

        $this->account = factory(App\Models\Mship\Account::class)->create([
            "id" => 123456,
            "name_first" => "John",
            "name_last" => "Doe",
            "email" => "i_sleep@gmail.com",
        ]);
    }

    /** @test */
    public function it_adds_a_state()
    {
        $divisionState = \App\Models\Mship\State::findByCode("DIVISION");

        $this->account->addState($divisionState);

        $this->seeInDatabase("mship_account_state", [
            "account_id" => $this->account->id,
            "state_id" => $divisionState->id,
            "end_at" => null,
        ]);
    }

    /** @test */
    public function it_deletes_old_permanent_state()
        {
            $divisionState = \App\Models\Mship\State::findByCode("DIVISION");
            $regionState = \App\Models\Mship\State::findByCode("REGION");

            $this->account->addState($regionState);

            $this->seeInDatabase("mship_account_state", [
                "account_id" => $this->account->id,
                "state_id" => $regionState->id,
                "end_at" => null,
            ]);

            $this->account->fresh()->addState($divisionState);

            $this->seeInDatabase("mship_account_state", [
                "account_id" => $this->account->id,
                "state_id" => $divisionState->id,
                "end_at" => null,
            ]);

            $this->notSeeInDatabase("mship_account_state", [
                "account_id" => $this->account->id,
                "state_id" => $regionState->id,
                "end_at" => null,
            ]);

            $this->seeInDatabase("mship_account_state", [
                "account_id" => $this->account->id,
                "state_id" => $regionState->id
            ]);
    }
}