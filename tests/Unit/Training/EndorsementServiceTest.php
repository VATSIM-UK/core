<?php

namespace Tests\Unit\Training;

use App\Models\Atc\Position;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use App\Services\Training\EndorsementService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EndorsementServiceTest extends TestCase
{
    use DatabaseTransactions;

    private TrainingPlace $trainingPlace;

    private Position $position;

    private Account $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = Account::factory()->create();

        // Create a position with a specific callsign (e.g., EGBB_TWR)
        $this->position = Position::factory()->create([
            'callsign' => 'EGBB_TWR',
        ]);

        // Create a training position linked to the position
        $trainingPosition = TrainingPosition::factory()->create([
            'position_id' => $this->position->id,
        ]);

        // Create waiting list and add student
        $waitingList = WaitingList::factory()->create();
        $waitingListAccount = $waitingList->addToWaitingList($this->student, $this->privacc);

        // Create a training place
        $this->trainingPlace = TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
            'created_at' => now()->subDays(30),
        ]);
    }

    public function test_get_solo_endorsements_for_training_place_returns_only_direct_position_matches()
    {
        // Create endorsement for the exact position
        $directEndorsement = Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $this->position->id,
            'expires_at' => now()->addDays(30),
            'created_at' => now()->subDays(10),
        ]);

        // Create another position with same suffix
        $relatedPosition = Position::factory()->create([
            'callsign' => 'EGLL_TWR',
        ]);

        // Create endorsement for related position (should not be included)
        Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $relatedPosition->id,
            'expires_at' => now()->addDays(30),
            'created_at' => now()->subDays(10),
        ]);

        $result = EndorsementService::getSoloEndorsementsForTrainingPlace($this->trainingPlace)->get();

        $this->assertCount(1, $result);
        $this->assertEquals($directEndorsement->id, $result->first()->id);
    }

    public function test_get_solo_endorsements_only_returns_endorsements_after_training_place_start_date()
    {
        // Create endorsement before training place started (should not be included)
        Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $this->position->id,
            'expires_at' => now()->addDays(30),
            'created_at' => now()->subDays(60), // Before training place created
        ]);

        // Create endorsement after training place started (should be included)
        $validEndorsement = Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $this->position->id,
            'expires_at' => now()->addDays(30),
            'created_at' => now()->subDays(10), // After training place created
        ]);

        $result = EndorsementService::getSoloEndorsementsForTrainingPlace($this->trainingPlace)->get();

        $this->assertCount(1, $result);
        $this->assertEquals($validEndorsement->id, $result->first()->id);
    }

    public function test_get_solo_endorsements_only_returns_endorsements_with_expiry_dates()
    {
        // Create permanent endorsement (should not be included)
        Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $this->position->id,
            'expires_at' => null,
            'created_at' => now()->subDays(10),
        ]);

        // Create solo endorsement with expiry (should be included)
        $soloEndorsement = Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $this->position->id,
            'expires_at' => now()->addDays(30),
            'created_at' => now()->subDays(10),
        ]);

        $result = EndorsementService::getSoloEndorsementsForTrainingPlace($this->trainingPlace)->get();

        $this->assertCount(1, $result);
        $this->assertEquals($soloEndorsement->id, $result->first()->id);
    }

    public function test_get_all_solo_endorsements_includes_related_positions_by_suffix()
    {
        // Create endorsement for the direct position
        $directEndorsement = Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $this->position->id,
            'expires_at' => now()->addDays(30),
            'created_at' => now()->subDays(10),
        ]);

        // Create positions with same suffix (TWR)
        $relatedPosition1 = Position::factory()->create([
            'callsign' => 'EGLL_TWR',
        ]);

        $relatedPosition2 = Position::factory()->create([
            'callsign' => 'EGKK_TWR',
        ]);

        // Create position with different suffix (should not be included)
        $differentPosition = Position::factory()->create([
            'callsign' => 'EGBB_APP',
        ]);

        // Create endorsements for related positions
        $relatedEndorsement1 = Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $relatedPosition1->id,
            'expires_at' => now()->addDays(30),
            'created_at' => now()->subDays(10),
        ]);

        $relatedEndorsement2 = Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $relatedPosition2->id,
            'expires_at' => now()->addDays(30),
            'created_at' => now()->subDays(10),
        ]);

        // Create endorsement for different suffix (should not be included)
        Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $differentPosition->id,
            'expires_at' => now()->addDays(30),
            'created_at' => now()->subDays(10),
        ]);

        $result = EndorsementService::getAllSoloEndorsementsIncludingRelatedPositionsForTrainingPlace($this->trainingPlace)->get();

        $this->assertCount(3, $result);
        $endorsementIds = $result->pluck('id')->toArray();
        $this->assertContains($directEndorsement->id, $endorsementIds);
        $this->assertContains($relatedEndorsement1->id, $endorsementIds);
        $this->assertContains($relatedEndorsement2->id, $endorsementIds);
    }

    public function test_get_all_solo_endorsements_adds_category_column()
    {
        // Create direct position endorsement
        Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $this->position->id,
            'expires_at' => now()->addDays(30),
            'created_at' => now()->subDays(10),
        ]);

        // Create related position endorsement
        $relatedPosition = Position::factory()->create([
            'callsign' => 'EGLL_TWR',
        ]);

        Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $relatedPosition->id,
            'expires_at' => now()->addDays(30),
            'created_at' => now()->subDays(10),
        ]);

        $result = EndorsementService::getAllSoloEndorsementsIncludingRelatedPositionsForTrainingPlace($this->trainingPlace)->get();

        // Check that endorsement_category column exists
        $this->assertTrue($result->first()->offsetExists('endorsement_category'));

        // Check categories are correct
        $directEndorsement = $result->where('endorsable_id', $this->position->id)->first();
        $relatedEndorsement = $result->where('endorsable_id', $relatedPosition->id)->first();

        $this->assertEquals('Training Place Position', $directEndorsement->endorsement_category);
        $this->assertEquals('Related Position by Rating', $relatedEndorsement->endorsement_category);
    }

    public function test_get_all_solo_endorsements_orders_direct_position_first()
    {
        // Create related position endorsement first
        $relatedPosition = Position::factory()->create([
            'callsign' => 'EGLL_TWR',
        ]);

        Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $relatedPosition->id,
            'expires_at' => now()->addDays(30),
            'created_at' => now()->subDays(15), // Created earlier
        ]);

        // Create direct position endorsement later
        Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $this->position->id,
            'expires_at' => now()->addDays(30),
            'created_at' => now()->subDays(5), // Created later
        ]);

        $result = EndorsementService::getAllSoloEndorsementsIncludingRelatedPositionsForTrainingPlace($this->trainingPlace)->get();

        // First result should be the direct position (Training Place Position)
        $this->assertEquals('Training Place Position', $result->first()->endorsement_category);
        $this->assertEquals($this->position->id, $result->first()->endorsable_id);

        // Second result should be the related position
        $this->assertEquals('Related Position by Rating', $result->last()->endorsement_category);
        $this->assertEquals($relatedPosition->id, $result->last()->endorsable_id);
    }

    public function test_get_solo_endorsements_only_returns_endorsements_for_correct_student()
    {
        // Create endorsement for the correct student
        $validEndorsement = Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $this->position->id,
            'expires_at' => now()->addDays(30),
            'created_at' => now()->subDays(10),
        ]);

        // Create endorsement for a different student
        $otherStudent = Account::factory()->create();
        Endorsement::factory()->create([
            'account_id' => $otherStudent->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $this->position->id,
            'expires_at' => now()->addDays(30),
            'created_at' => now()->subDays(10),
        ]);

        $result = EndorsementService::getSoloEndorsementsForTrainingPlace($this->trainingPlace)->get();

        $this->assertCount(1, $result);
        $this->assertEquals($validEndorsement->id, $result->first()->id);
        $this->assertEquals($this->student->id, $result->first()->account_id);
    }

    public function test_get_all_solo_endorsements_returns_empty_when_no_matching_endorsements()
    {
        // Don't create any endorsements

        $result = EndorsementService::getAllSoloEndorsementsIncludingRelatedPositionsForTrainingPlace($this->trainingPlace)->get();

        $this->assertCount(0, $result);
    }

    public function test_get_all_solo_endorsements_includes_endorsements_before_training_place_start_date()
    {
        // Create endorsement BEFORE training place started (should be included)
        $oldEndorsement = Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $this->position->id,
            'expires_at' => now()->addDays(30),
            'created_at' => now()->subDays(60), // Before training place created (created at -30 days)
        ]);

        // Create endorsement AFTER training place started (should also be included)
        $newEndorsement = Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $this->position->id,
            'expires_at' => now()->addDays(30),
            'created_at' => now()->subDays(10), // After training place created
        ]);

        // Create related position endorsement before training place
        $relatedPosition = Position::factory()->create([
            'callsign' => 'EGLL_TWR',
        ]);

        $oldRelatedEndorsement = Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $relatedPosition->id,
            'expires_at' => now()->addDays(30),
            'created_at' => now()->subDays(90), // Long before training place created
        ]);

        $result = EndorsementService::getAllSoloEndorsementsIncludingRelatedPositionsForTrainingPlace($this->trainingPlace)->get();

        // All three endorsements should be included regardless of creation date
        $this->assertCount(3, $result);
        $endorsementIds = $result->pluck('id')->toArray();
        $this->assertContains($oldEndorsement->id, $endorsementIds);
        $this->assertContains($newEndorsement->id, $endorsementIds);
        $this->assertContains($oldRelatedEndorsement->id, $endorsementIds);
    }

    public function test_has_active_solo_endorsement_returns_true_when_active_endorsement_exists()
    {
        // Create an active endorsement
        Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $this->position->id,
            'expires_at' => now()->addDays(30),
        ]);

        $result = EndorsementService::hasActiveSoloEndorsement($this->position, $this->student);

        $this->assertTrue($result);
    }

    public function test_has_active_solo_endorsement_returns_false_when_no_endorsement_exists()
    {
        // Don't create any endorsements

        $result = EndorsementService::hasActiveSoloEndorsement($this->position, $this->student);

        $this->assertFalse($result);
    }

    public function test_has_active_solo_endorsement_returns_false_when_endorsement_has_expired()
    {
        // Create an expired endorsement
        Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $this->position->id,
            'expires_at' => now()->subDays(1), // Expired yesterday
        ]);

        $result = EndorsementService::hasActiveSoloEndorsement($this->position, $this->student);

        $this->assertFalse($result);
    }

    public function test_has_active_solo_endorsement_returns_false_for_different_position()
    {
        // Create endorsement for a different position
        $differentPosition = Position::factory()->create([
            'callsign' => 'EGLL_APP',
        ]);

        Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $differentPosition->id,
            'expires_at' => now()->addDays(30),
        ]);

        $result = EndorsementService::hasActiveSoloEndorsement($this->position, $this->student);

        $this->assertFalse($result);
    }

    public function test_has_active_solo_endorsement_returns_false_for_different_account()
    {
        // Create endorsement for a different account
        $differentAccount = Account::factory()->create();

        Endorsement::factory()->create([
            'account_id' => $differentAccount->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $this->position->id,
            'expires_at' => now()->addDays(30),
        ]);

        $result = EndorsementService::hasActiveSoloEndorsement($this->position, $this->student);

        $this->assertFalse($result);
    }

    public function test_has_active_solo_endorsement_returns_false_for_permanent_endorsement()
    {
        // Create a permanent endorsement (no expiry date)
        Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $this->position->id,
            'expires_at' => null, // Permanent endorsement
        ]);

        $result = EndorsementService::hasActiveSoloEndorsement($this->position, $this->student);

        $this->assertFalse($result);
    }

    public function test_has_active_solo_endorsement_returns_true_when_endorsement_expires_today()
    {
        // Create an endorsement that expires at the end of today
        Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $this->position->id,
            'expires_at' => now()->endOfDay(),
        ]);

        $result = EndorsementService::hasActiveSoloEndorsement($this->position, $this->student);

        $this->assertTrue($result);
    }

    public function test_has_active_solo_endorsement_returns_true_when_multiple_endorsements_and_one_is_active()
    {
        // Create an expired endorsement
        Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $this->position->id,
            'expires_at' => now()->subDays(10),
        ]);

        // Create an active endorsement
        Endorsement::factory()->create([
            'account_id' => $this->student->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $this->position->id,
            'expires_at' => now()->addDays(30),
        ]);

        $result = EndorsementService::hasActiveSoloEndorsement($this->position, $this->student);

        $this->assertTrue($result);
    }
}
