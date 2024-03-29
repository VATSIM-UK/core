<?php

namespace Tests\Unit\Roster;

use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use App\Models\Roster;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AccountCanControlTest extends TestCase
{
    public function setUp(): void
    {
        Event::fake();
    }

    public function test_detects_can_control_with_rating_when_home_member()
    {
        $qualification = Qualification::code('S2')->first();
        $account = Account::factory()->create();
        $account->addQualification($qualification);
        $account->addState(State::findByCode('DIVISION'));

        $position = Position::factory()->create([
            'type' => Position::TYPE_TOWER,
        ]);

        $roster = Roster::create([
            'account_id' => $account->id,
        ]);

        $this->assertTrue($roster->accountCanControl($position));
    }

    public function test_cannot_control_without_rating_when_home_member()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $account->addQualification(Qualification::code('S1')->first());

        $position = Position::factory()->create([
            'type' => Position::TYPE_TOWER,
        ]);

        $roster = Roster::create([
            'account_id' => $account->id,
        ]);

        $this->assertFalse($roster->accountCanControl($position));
    }

    public function test_detects_cannot_control_with_rating_when_visiting_without_endorsement()
    {
        $qualification = Qualification::code('S2')->first();
        $account = Account::factory()->create();
        $account->addQualification($qualification);
        $account->addState(State::findByCode('VISITING'));

        $position = Position::factory()->create([
            'type' => Position::TYPE_TOWER,
        ]);

        $roster = Roster::create([
            'account_id' => $account->id,
        ]);

        $this->assertFalse($roster->accountCanControl($position));
    }

    public function test_detects_cannot_control_with_rating_when_transferring_without_endorsement()
    {
        $qualification = Qualification::code('S2')->first();
        $account = Account::factory()->create();
        $account->addQualification($qualification);
        $account->addState(State::findByCode('TRANSFERRING'));

        $position = Position::factory()->create([
            'type' => Position::TYPE_TOWER,
        ]);

        $roster = Roster::create([
            'account_id' => $account->id,
        ]);

        $this->assertFalse($roster->accountCanControl($position));
    }

    public function test_detects_can_control_with_rating_when_visiting_with_endorsement()
    {
        $qualification = Qualification::code('S2')->first();
        $account = Account::factory()->create();
        $account->addQualification($qualification);
        $account->addState(State::findByCode('VISITING'));

        $position = Position::factory()->create([
            'type' => Position::TYPE_TOWER,
        ]);

        $roster = Roster::create([
            'account_id' => $account->id,
        ]);

        Endorsement::create([
            'account_id' => $account->id,
            'endorsable_type' => Qualification::class,
            'endorsable_id' => $qualification->id,
            'created_by' => $this->privacc->id,
        ]);

        $this->assertTrue($roster->accountCanControl($position));
    }

    public function test_detects_can_control_with_rating_when_transferring_with_endorsement()
    {
        $qualification = Qualification::code('S2')->first();
        $account = Account::factory()->create();
        $account->addQualification($qualification);
        $account->addState(State::findByCode('TRANSFERRING'));

        $position = Position::factory()->create([
            'type' => Position::TYPE_TOWER,
        ]);

        $roster = Roster::create([
            'account_id' => $account->id,
        ]);

        Endorsement::create([
            'account_id' => $account->id,
            'endorsable_type' => Qualification::class,
            'endorsable_id' => $qualification->id,
            'created_by' => $this->privacc->id,
        ]);

        $this->assertTrue($roster->accountCanControl($position));
    }

    public function test_detects_cannot_control_if_region_member()
    {
        $qualification = Qualification::code('S2')->first();
        $account = Account::factory()->create();
        $account->addQualification($qualification);
        $account->addState(State::findByCode('REGION'));

        $position = Position::factory()->create([
            'type' => Position::TYPE_TOWER,
        ]);

        $roster = Roster::create([
            'account_id' => $account->id,
        ]);

        $this->assertFalse($roster->accountCanControl($position));
    }

    public function test_detects_cannot_control_if_international_member()
    {
        $qualification = Qualification::code('S2')->first();
        $account = Account::factory()->create();
        $account->addQualification($qualification);
        $account->addState(State::findByCode('INTERNATIONAL'));

        $position = Position::factory()->create([
            'type' => Position::TYPE_TOWER,
        ]);

        $roster = Roster::create([
            'account_id' => $account->id,
        ]);

        $this->assertFalse($roster->accountCanControl($position));
    }

    public function test_handles_position_being_part_of_multiple_position_groups_when_endorsed()
    {
        $position = Position::factory()->create();

        $positionGroup1 = PositionGroup::factory()->create();
        $positionGroup2 = PositionGroup::factory()->create(['id' => $positionGroup1->id + 1]);
        $positionGroup1->positions()->attach($position);
        $positionGroup2->positions()->attach($position);

        Endorsement::create([
            'account_id' => $this->user->id,
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => $positionGroup2->id,
            'created_by' => $this->privacc->id,
        ]);

        $roster = Roster::create([
            'account_id' => $this->user->id,
        ]);

        $this->assertTrue($roster->accountCanControl($position));
    }

    public function test_detects_can_control_with_position_group_assigned()
    {
        $position = Position::factory()->create();
        $positionGroup = PositionGroup::factory()->create();
        $positionGroup->positions()->attach($position);

        Endorsement::create([
            'account_id' => $this->user->id,
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => $positionGroup->id,
            'created_by' => $this->privacc->id,
        ]);

        $roster = Roster::create([
            'account_id' => $this->user->id,
        ]);

        $this->assertTrue($roster->accountCanControl($position));
    }

    public function test_detects_cannot_control_with_position_group_not_assigned()
    {
        $position = Position::factory()->create();
        $positionGroup = PositionGroup::factory()->create();
        $positionGroup->positions()->attach($position);

        $account = Account::factory()->create();
        $account->addQualification(Qualification::code('S2')->first());
        $account->addState(State::findByCode('DIVISION'));
        $roster = Roster::create([
            'account_id' => $account->id,
        ]);

        $this->assertFalse($roster->accountCanControl($position));
    }

    public function test_detects_cannot_control_with_position_group_not_assigned_if_rated()
    {
        $position = Position::factory()->create(['type' => Position::TYPE_TOWER]);
        $positionGroup = PositionGroup::factory()->create();
        $positionGroup->positions()->attach($position);

        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $account->addQualification(Qualification::code('S2')->first());
        $roster = Roster::create([
            'account_id' => $account->id,
        ]);

        $this->assertFalse($roster->accountCanControl($position));
    }

    public function test_detects_cannot_control_position_when_not_grated_to_user_as_visitor_even_if_rated()
    {
        $position = Position::factory(['type' => Position::TYPE_TOWER])->create();
        $positionGroup = PositionGroup::factory()->create();
        $positionGroup->positions()->attach($position);

        $visitorAccount = Account::factory()->create();
        $visitorAccount->addQualification(Qualification::code('S2')->first());
        $visitorAccount->addState(State::findByCode('VISITING'));

        $roster = Roster::create([
            'account_id' => $visitorAccount->id,
        ]);

        $this->assertFalse($roster->accountCanControl($position));
    }

    public function test_detects_cannot_control_position_when_not_grated_to_user_as_transferring_even_if_rated()
    {
        $position = Position::factory(['type' => Position::TYPE_TOWER])->create();
        $positionGroup = PositionGroup::factory()->create();
        $positionGroup->positions()->attach($position);

        $transferringAccount = Account::factory()->create();
        $transferringAccount->addQualification(Qualification::code('S2')->first());
        $transferringAccount->addState(State::findByCode('TRANSFERRING'));

        $roster = Roster::create([
            'account_id' => $transferringAccount->id,
        ]);

        $this->assertFalse($roster->accountCanControl($position));
    }

    public function test_detects_solo_endorsement_when_position_above_rating()
    {
        $qualification = Qualification::code('S2')->first();
        Qualification::code('S3')->first();

        $account = Account::factory()->create();
        $account->addQualification($qualification);

        // approach exceeds the 'S2' requirements' VATSIM attribute.
        $position = Position::factory()->create([
            'type' => Position::TYPE_APPROACH,
            'temporarily_endorsable' => true,
        ]);

        $roster = Roster::create([
            'account_id' => $account->id,
        ]);

        Endorsement::create([
            'account_id' => $account->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $position->id,
            'created_by' => $this->privacc->id,
            'expires_at' => now()->addDays(1),
        ]);

        $this->assertTrue($roster->accountCanControl($position));
    }

    public function test_detects_when_solo_endorsement_expired()
    {
        $qualification = Qualification::code('S2')->first();

        $account = Account::factory()->create();
        $account->addQualification($qualification);
        $account->addState(State::findByCode('DIVISION'));

        $roster = Roster::create([
            'account_id' => $account->id,
        ]);

        // approach exceeds the 'S2' requirements' VATSIM attribute.
        $position = Position::factory()->create([
            'type' => Position::TYPE_APPROACH,
            'temporarily_endorsable' => true,
        ]);

        Endorsement::create([
            'account_id' => $account->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $position->id,
            'created_by' => $this->privacc->id,
            'expires_at' => now()->subDays(1),
        ]);

        $this->assertFalse($roster->accountCanControl($position));
    }

    public function test_detects_can_control_with_direct_endorsement_without_expiry()
    {
        $position = Position::factory()->create();

        $roster = Roster::create([
            'account_id' => $this->user->id,
        ]);

        Endorsement::create([
            'account_id' => $this->user->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $position->id,
            'created_by' => $this->privacc->id,
        ]);

        $this->assertTrue($roster->accountCanControl($position));
    }

    public function test_can_control_when_endorsement_not_assigned_but_rating_facilitates()
    {
        $lowerQualification = Qualification::code('S1')->first();
        $qualification = Qualification::code('S2')->first();
        $account = Account::factory()->create();
        $account->addQualification($qualification);
        $account->addState(State::findByCode('DIVISION'));

        $position = Position::factory()->create([
            'type' => Position::TYPE_TOWER,
        ]);

        $positionGroup = PositionGroup::factory()->create(['maximum_atc_qualification_id' => $lowerQualification->id]);
        $positionGroup->positions()->attach($position);

        $roster = Roster::create([
            'account_id' => $account->id,
        ]);

        $this->assertTrue($roster->accountCanControl($position));
    }

    public function test_can_control_when_endorsed_to_qualification()
    {
        $qualification = Qualification::code('S2')->first();
        $account = Account::factory()->create();
        $account->addQualification($qualification);
        $account->addState(State::findByCode('VISITING'));

        $position = Position::factory()->create([
            'type' => Position::TYPE_TOWER,
        ]);

        Endorsement::create([
            'account_id' => $account->id,
            'endorsable_type' => Qualification::class,
            'endorsable_id' => $qualification->id,
            'created_by' => $this->privacc->id,
        ]);

        $roster = Roster::create([
            'account_id' => $account->id,
        ]);

        $this->assertTrue($roster->accountCanControl($position));
    }
}
