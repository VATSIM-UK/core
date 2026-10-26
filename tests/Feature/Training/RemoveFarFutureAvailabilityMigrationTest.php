<?php

declare(strict_types=1);

namespace Tests\Feature\Training;

use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\Availability;
use App\Models\Training\TrainingPlace\AvailabilityLogEntry;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RemoveFarFutureAvailabilityMigrationTest extends TestCase
{
    use DatabaseTransactions;

    private function runMigration(): void
    {
        $migration = require database_path('migrations/2026_10_18_000000_remove_far_future_availability.php');

        $migration->up();
    }

    #[Test]
    public function it_deletes_rows_past_the_window_and_supersedes_their_log_entries(): void
    {
        $this->travelTo(Carbon::create(2026, 5, 13, 12, 0, 0));

        $account = Account::factory()->create();
        $member = Member::factory()->recycle($account)->create(['cid' => $account->id]);
        $place = TrainingPlace::factory()->createQuietly(['account_id' => $account->id]);

        $row = Availability::factory()->forStudent($member->id)->create([
            'date' => '2026-07-01', 'from' => '18:00:00', 'to' => '21:00:00', 'type' => 'S',
        ]);
        $entry = AvailabilityLogEntry::factory()->create([
            'training_place_id' => $place->id,
            'event' => 'added',
            'slot_from' => '2026-07-01 18:00:00',
            'slot_to' => '2026-07-01 21:00:00',
            'created_at' => now()->subDay(),
            'superseded_at' => null,
        ]);

        $this->runMigration();

        $this->assertNull($row->fresh());
        $this->assertNotNull($entry->fresh()->superseded_at);
    }

    #[Test]
    public function it_keeps_rows_on_the_last_day_of_the_window(): void
    {
        $this->travelTo(Carbon::create(2026, 5, 13, 12, 0, 0));

        $member = Member::factory()->create();
        $row = Availability::factory()->forStudent($member->id)->create([
            'date' => '2026-06-13', 'from' => '18:00:00', 'to' => '21:00:00', 'type' => 'S',
        ]);

        $this->runMigration();

        $this->assertNotNull($row->fresh());
    }

    #[Test]
    public function it_still_deletes_rows_for_a_student_with_no_training_place(): void
    {
        $this->travelTo(Carbon::create(2026, 5, 13, 12, 0, 0));

        $member = Member::factory()->create();
        $row = Availability::factory()->forStudent($member->id)->create([
            'date' => '2026-07-01', 'from' => '18:00:00', 'to' => '21:00:00', 'type' => 'S',
        ]);

        $this->runMigration();

        $this->assertNull($row->fresh());
    }

    #[Test]
    public function it_supersedes_log_entries_for_a_revoked_training_place(): void
    {
        $this->travelTo(Carbon::create(2026, 5, 13, 12, 0, 0));

        $account = Account::factory()->create();
        $member = Member::factory()->recycle($account)->create(['cid' => $account->id]);
        $place = TrainingPlace::factory()->createQuietly(['account_id' => $account->id]);
        $place->delete();

        $row = Availability::factory()->forStudent($member->id)->create([
            'date' => '2026-07-01', 'from' => '18:00:00', 'to' => '21:00:00', 'type' => 'S',
        ]);
        $entry = AvailabilityLogEntry::factory()->create([
            'training_place_id' => $place->id,
            'event' => 'added',
            'slot_from' => '2026-07-01 18:00:00',
            'slot_to' => '2026-07-01 21:00:00',
            'created_at' => now()->subDay(),
            'superseded_at' => null,
        ]);

        $this->runMigration();

        $this->assertNull($row->fresh());
        $this->assertNotNull($entry->fresh()->superseded_at);
    }
}
