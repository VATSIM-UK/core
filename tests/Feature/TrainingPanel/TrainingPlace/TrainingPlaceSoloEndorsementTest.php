<?php

namespace Tests\Feature\TrainingPanel\TrainingPlace;

use App\Libraries\UKCP;
use App\Livewire\Training\TrainingPlaceSoloEndorsement;
use App\Models\Atc\Position;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement;
use App\Models\Mship\State;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class TrainingPlaceSoloEndorsementTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $this->panelUser->givePermissionTo('endorsement.create.temporary');

        Livewire::actingAs($this->panelUser);
    }

    #[Test]
    public function it_only_offers_positions_returned_by_ukcp_top_down()
    {
        $trainingPlace = $this->createTrainingPlace();
        $topDownPosition = Position::factory()->temporarilyEndorsable()->create([
            'callsign' => 'SCL_TMA_CTR',
            'ukcp_position_id' => 10,
        ]);
        $otherPosition = Position::factory()->temporarilyEndorsable()->create([
            'callsign' => 'SCL_N_CTR',
            'ukcp_position_id' => 11,
        ]);

        $this->mock(UKCP::class, function (MockInterface $mock) use ($topDownPosition) {
            $mock->shouldReceive('getTopDownPositionsForPosition')
                ->with('SCO_CTR')
                ->andReturn(collect([
                    (object) [
                        'id' => $topDownPosition->ukcp_position_id,
                        'callsign' => $topDownPosition->callsign,
                        'frequency' => 123.400,
                    ],
                ]));
        });

        Livewire::test(TrainingPlaceSoloEndorsement::class, ['trainingPlace' => $trainingPlace])
            ->mountTableAction('issue_solo_endorsement')
            ->assertMountedActionModalSee('SCL_TMA_CTR')
            ->assertMountedActionModalDontSee($otherPosition->callsign);
    }

    #[Test]
    public function it_issues_a_solo_endorsement_for_a_related_position_with_notes()
    {
        $trainingPlace = $this->createTrainingPlace();
        $relatedPosition = Position::factory()->temporarilyEndorsable()->create([
            'callsign' => 'SCL_TMA_CTR',
        ]);

        Livewire::test(TrainingPlaceSoloEndorsement::class, ['trainingPlace' => $trainingPlace])
            ->callTableAction('issue_solo_endorsement', data: [
                'position_id' => $relatedPosition->id,
                'days' => 7,
                'notes' => 'Only during daytime.',
            ])
            ->assertNotified('Solo endorsement issued for SCL_TMA_CTR');

        $this->assertDatabaseHas('mship_account_endorsement', [
            'account_id' => $trainingPlace->account_id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $relatedPosition->id,
        ]);

        $this->assertDatabaseHas('mship_account_note', [
            'account_id' => $trainingPlace->account_id,
            'content' => 'Solo endorsement issued for SCL_TMA_CTR for 7 days. Notes: Only during daytime.',
        ]);
    }

    #[Test]
    public function it_issues_a_solo_endorsement_for_the_training_position()
    {
        $trainingPlace = $this->createTrainingPlace();

        Livewire::test(TrainingPlaceSoloEndorsement::class, ['trainingPlace' => $trainingPlace])
            ->callTableAction('issue_solo_endorsement', data: [
                'position_id' => $trainingPlace->trainingPosition->position_id,
                'days' => 7,
            ]);

        $this->assertDatabaseHas('mship_account_endorsement', [
            'account_id' => $trainingPlace->account_id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $trainingPlace->trainingPosition->position_id,
        ]);
    }

    #[Test]
    public function it_does_not_issue_a_duplicate_solo_endorsement()
    {
        $trainingPlace = $this->createTrainingPlace();

        Endorsement::factory()->create([
            'account_id' => $trainingPlace->account_id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $trainingPlace->trainingPosition->position_id,
            'expires_at' => now()->addDays(7),
        ]);

        Livewire::test(TrainingPlaceSoloEndorsement::class, ['trainingPlace' => $trainingPlace])
            ->callTableAction('issue_solo_endorsement', data: [
                'position_id' => $trainingPlace->trainingPosition->position_id,
                'days' => 7,
            ])
            ->assertNotified('Unable to issue solo endorsement');

        $this->assertDatabaseCount('mship_account_endorsement', 1);
    }

    private function createTrainingPlace(): TrainingPlace
    {
        $student = Account::factory()->create();
        $student->addState(State::findByCode('DIVISION'));
        Member::factory()->forAccount($student)->create();

        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $waitingListAccount = $waitingList->addToWaitingList($student, $this->panelUser);

        $position = Position::factory()->temporarilyEndorsable()->create([
            'callsign' => 'SCO_CTR',
        ]);

        $trainingPosition = TrainingPosition::factory()
            ->withCtsPositions(['SCO_CTR'])
            ->create([
                'position_id' => $position->id,
                'created_at' => now()->subDays(14),
            ]);

        return TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'account_id' => $student->id,
            'training_position_id' => $trainingPosition->id,
        ]);
    }
}
