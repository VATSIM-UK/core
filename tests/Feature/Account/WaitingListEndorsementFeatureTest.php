<?php

namespace Tests\Feature\Account;

use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WaitingListEndorsementFeatureTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_prevents_self_enrolment_without_required_endorsement()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $waitingList = WaitingList::factory()->create([
            'self_enrolment_enabled' => true,
            'requires_roster_membership' => false,
            'required_endorsement_id' => PositionGroup::factory()->create()->id,
        ]);

        $this->actingAs($account)
            ->post(route('mship.waiting-lists.self-enrol', $waitingList))
            ->assertStatus(403);

        $this->assertFalse($waitingList->includesAccount($account));
    }

    #[Test]
    public function it_allows_self_enrolment_with_required_endorsement()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $positionGroup = PositionGroup::factory()->create();
        $account->endorsements()->create([
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => $positionGroup->id,
        ]);

        $waitingList = WaitingList::factory()->create([
            'self_enrolment_enabled' => true,
            'requires_roster_membership' => false,
            'required_endorsement_id' => $positionGroup->id,
        ]);

        $this->actingAs($account)
            ->post(route('mship.waiting-lists.self-enrol', $waitingList))
            ->assertRedirect(route('mship.waiting-lists.index'))
            ->assertSessionHas('success');

        $this->assertTrue($waitingList->includesAccount($account));
    }

    #[Test]
    public function it_allows_self_enrolement_when_user_has_multiple_endorsements_and_one_matches()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $positionGroup1 = PositionGroup::factory()->create();
        $positionGroup2 = PositionGroup::factory()->create();

        $account->endorsements()->createMany([
            [
                'endorsable_type' => PositionGroup::class,
                'endorsable_id' => $positionGroup1->id,
            ],
            [
                'endorsable_type' => PositionGroup::class,
                'endorsable_id' => $positionGroup2->id,
            ],
        ]);

        $waitingList = WaitingList::factory()->create([
            'self_enrolment_enabled' => true,
            'requires_roster_membership' => false,
            'required_endorsement_id' => $positionGroup2->id,
        ]);

        $this->actingAs($account)
            ->post(route('mship.waiting-lists.self-enrol', $waitingList))
            ->assertRedirect(route('mship.waiting-lists.index'))
            ->assertSessionHas('success');

        $this->assertTrue($waitingList->includesAccount($account));
    }

    #[Test]
    public function it_prevents_self_enrolement_when_list_full_even_with_required_endorsement()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $positionGroup = PositionGroup::factory()->create();
        $account->endorsements()->create([
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => $positionGroup->id,
        ]);

        $waitingList = WaitingList::factory()->create([
            'self_enrolment_enabled' => true,
            'requires_roster_membership' => false,
            'required_endorsement_id' => $positionGroup->id,
            'max_capacity' => 1,
        ]);

        $otherAccount = Account::factory()->create();
        $otherAccount->addState(State::findByCode('DIVISION'));
        $otherAccount->endorsements()->create([
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => $positionGroup->id,
        ]);
        $waitingList->addToWaitingList($otherAccount, $this->privacc);

        $this->actingAs($account)
            ->post(route('mship.waiting-lists.self-enrol', $waitingList))
            ->assertStatus(403);

        $this->assertFalse($waitingList->includesAccount($account));
    }

    #[Test]
    public function it_prevents_self_enrolement_when_user_has_endorsement_but_fails_qualification_rules()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $positionGroup = PositionGroup::factory()->create();
        $account->endorsements()->create([
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => $positionGroup->id,
        ]);
        $account->addQualification(Qualification::factory()->create(['vatsim' => 2]));

        $waitingList = WaitingList::factory()->create([
            'self_enrolment_enabled' => true,
            'requires_roster_membership' => false,
            'required_endorsement_id' => $positionGroup->id,
            'self_enrolment_minimum_qualification_id' => Qualification::factory()->create(['vatsim' => 3])->id,
        ]);

        $this->actingAs($account)
            ->post(route('mship.waiting-lists.self-enrol', $waitingList))
            ->assertStatus(403);

        $this->assertFalse($waitingList->includesAccount($account));
    }
}
