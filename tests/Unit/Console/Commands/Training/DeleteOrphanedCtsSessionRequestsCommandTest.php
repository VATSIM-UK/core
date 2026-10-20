<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands\Training;

use App\Models\Cts\Member;
use App\Models\Cts\Session as CtsSession;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteOrphanedCtsSessionRequestsCommandTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Local databases can already hold active training places. Clear their
        // orphaned requests inside this transaction so later counts are exact.
        $this->artisan('training-places:delete-orphaned-cts-session-requests')->assertSuccessful();
    }

    #[Test]
    public function it_deletes_untaken_requests_on_matching_callsigns_only(): void
    {
        $member = Member::factory()->create();
        $account = Account::factory()->createQuietly(['id' => $member->cid]);

        $trainingPosition = TrainingPosition::factory()->create([
            'cts_positions' => ['EGLL_APP', 'EGLL_TWR'],
            'cts_primary_position' => 'EGLL_APP',
        ]);

        TrainingPlace::factory()->createQuietly([
            'account_id' => $account->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        $primaryRequest = $this->ctsSession($member, 'EGLL_APP');
        $secondaryRequest = $this->ctsSession($member, 'EGLL_TWR');
        $otherCallsignRequest = $this->ctsSession($member, 'EGKK_APP');
        $bookedSession = $this->ctsSession($member, 'EGLL_APP', [
            'taken_date' => now()->addDay()->toDateString(),
            'session_done' => 0,
        ]);
        $filedSession = $this->ctsSession($member, 'EGLL_APP', [
            'session_done' => 1,
        ]);

        $this->artisan('training-places:delete-orphaned-cts-session-requests')
            ->expectsOutput('Deleted 2 orphaned CTS session request(s).')
            ->assertSuccessful();

        $this->assertDatabaseMissing('sessions', ['id' => $primaryRequest->id], 'cts');
        $this->assertDatabaseMissing('sessions', ['id' => $secondaryRequest->id], 'cts');
        $this->assertDatabaseHas('sessions', ['id' => $otherCallsignRequest->id], 'cts');
        $this->assertDatabaseHas('sessions', ['id' => $bookedSession->id], 'cts');
        $this->assertDatabaseHas('sessions', ['id' => $filedSession->id], 'cts');
    }

    #[Test]
    public function it_leaves_requests_for_members_without_an_active_training_place(): void
    {
        $member = Member::factory()->create();
        $request = $this->ctsSession($member, 'EGLL_APP');

        $this->artisan('training-places:delete-orphaned-cts-session-requests')
            ->expectsOutput('Deleted 0 orphaned CTS session request(s).')
            ->assertSuccessful();

        $this->assertDatabaseHas('sessions', ['id' => $request->id], 'cts');
    }

    #[Test]
    public function it_does_not_delete_requests_for_a_removed_training_place(): void
    {
        $member = Member::factory()->create();
        $account = Account::factory()->createQuietly(['id' => $member->cid]);

        $trainingPosition = TrainingPosition::factory()->create([
            'cts_positions' => ['EGPH_APP', 'EGPH_TWR'],
            'cts_primary_position' => 'EGPH_APP',
        ]);

        $trainingPlace = TrainingPlace::factory()->createQuietly([
            'account_id' => $account->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        $secondaryRequest = $this->ctsSession($member, 'EGPH_TWR');

        TrainingPlace::withoutEvents(fn () => $trainingPlace->delete());

        $this->artisan('training-places:delete-orphaned-cts-session-requests')
            ->expectsOutput('Deleted 0 orphaned CTS session request(s).')
            ->assertSuccessful();

        $this->assertDatabaseHas('sessions', ['id' => $secondaryRequest->id], 'cts');
    }

    #[Test]
    public function it_skips_training_places_without_a_cts_member(): void
    {
        $account = Account::factory()->createQuietly();

        $trainingPosition = TrainingPosition::factory()->create([
            'cts_positions' => ['EGCC_APP'],
            'cts_primary_position' => 'EGCC_APP',
        ]);

        TrainingPlace::factory()->createQuietly([
            'account_id' => $account->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        $this->artisan('training-places:delete-orphaned-cts-session-requests')
            ->expectsOutput('Deleted 0 orphaned CTS session request(s).')
            ->assertSuccessful();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function ctsSession(Member $member, string $position, array $overrides = []): CtsSession
    {
        return CtsSession::factory()->create(array_merge([
            'student_id' => $member->id,
            'position' => $position,
            'taken_date' => null,
            'session_done' => 0,
        ], $overrides));
    }
}
