<?php

namespace Tests\Feature\Training;

use App\Models\Cts\Member;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AvailabilityBackfillTest extends TestCase
{
    use DatabaseTransactions;

    private function runBackfill(): void
    {
        $migration = require database_path('migrations/2026_10_29_000000_backfill_training_availability_from_cts.php');

        $migration->up();
    }

    private function ctsSlot(Member $member, string $date, string $from, string $to, string $type = 'S'): void
    {
        DB::connection('cts')->table('availability')->insert([
            'student_id' => $member->id,
            'type' => $type,
            'date' => $date,
            'from' => $from,
            'to' => $to,
        ]);
    }

    private function backfilled(int $accountId): array
    {
        return DB::table('training_availability')
            ->where('account_id', $accountId)
            ->orderBy('starts_at')
            ->get()
            ->map(fn (object $row) => [$row->starts_at, $row->ends_at])
            ->all();
    }

    #[Test]
    public function it_keys_slots_by_cid_not_by_the_cts_member_id(): void
    {
        $account = Account::factory()->createQuietly();
        $member = Member::factory()->forAccount($account)->create();
        $date = now()->addDay()->toDateString();

        $this->ctsSlot($member, $date, '18:00:00', '20:00:00');

        $this->runBackfill();

        $this->assertSame([["{$date} 18:00:00", "{$date} 20:00:00"]], $this->backfilled($account->id));
        $this->assertSame([], $this->backfilled($member->id));
    }

    #[Test]
    public function it_is_idempotent(): void
    {
        $account = Account::factory()->createQuietly();
        $member = Member::factory()->forAccount($account)->create();

        $this->ctsSlot($member, now()->addDay()->toDateString(), '18:00:00', '20:00:00');

        $this->runBackfill();
        $this->runBackfill();

        $this->assertCount(1, $this->backfilled($account->id));
    }

    #[Test]
    public function it_maps_the_legacy_end_of_day_values(): void
    {
        $account = Account::factory()->createQuietly();
        $member = Member::factory()->forAccount($account)->create();
        $date = now()->addDay()->toDateString();
        $nextDay = now()->addDays(2)->toDateString();

        $this->ctsSlot($member, $date, '09:00:00', '24:00:00');
        $this->ctsSlot($member, $date, '10:00:00', '00:00:00');
        $this->ctsSlot($member, $date, '22:00:00', '02:00:00');

        $this->runBackfill();

        $this->assertSame([
            ["{$date} 09:00:00", "{$date} 23:45:00"],
            ["{$date} 10:00:00", "{$nextDay} 00:00:00"],
            ["{$date} 22:00:00", "{$nextDay} 02:00:00"],
        ], $this->backfilled($account->id));
    }

    #[Test]
    public function it_keeps_a_slot_that_opened_yesterday_and_is_still_open(): void
    {
        $this->travelTo(Carbon::create(2026, 11, 2, 1, 0));

        $account = Account::factory()->createQuietly();
        $member = Member::factory()->forAccount($account)->create();

        $this->ctsSlot($member, '2026-11-01', '22:00:00', '02:00:00');

        $this->runBackfill();

        $this->assertSame(
            [['2026-11-01 22:00:00', '2026-11-02 02:00:00']],
            $this->backfilled($account->id),
        );
    }

    #[Test]
    public function it_skips_rows_it_cannot_translate(): void
    {
        $account = Account::factory()->createQuietly();
        $member = Member::factory()->forAccount($account)->create();
        $unlinkedMember = Member::factory()->create();
        $date = now()->addDay()->toDateString();

        $this->ctsSlot($member, $date, '09:00:00', '10:00:00', 'M');
        $this->ctsSlot($member, $date, '11:00:00', '11:00:00');
        $this->ctsSlot($unlinkedMember, $date, '12:00:00', '13:00:00');
        $this->ctsSlot($member, now()->subDay()->toDateString(), '14:00:00', '15:00:00');

        $this->runBackfill();

        $this->assertSame([], $this->backfilled($account->id));
    }
}
