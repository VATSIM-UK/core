<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Training\Availability;

use App\Models\Cts\Availability;
use App\Services\Training\Availability\AvailabilityChangeType;
use App\Services\Training\Availability\AvailabilityPlanner;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AvailabilityPlannerTest extends TestCase
{
    private function row(string $date, string $from, string $to): Availability
    {
        return Availability::factory()->make([
            'date' => $date, 'from' => $from, 'to' => $to, 'type' => 'S', 'student_id' => 1,
        ]);
    }

    /** $count consecutive 15-minute cells starting at $from on $date. */
    private function cells(string $date, string $from, int $count): array
    {
        $out = [];
        $cursor = Carbon::parse("{$date} {$from}", 'UTC');
        for ($i = 0; $i < $count; $i++) {
            $out[] = $cursor->copy()->addMinutes(15 * $i)->format('Y-m-d H:i');
        }

        return $out;
    }

    #[Test]
    public function it_seeds_a_row_into_cells_within_the_window(): void
    {
        $plan = (new AvailabilityPlanner)->seed(['2026-10-16'], collect([$this->row('2026-10-16', '18:00:00', '21:00:00')]), Carbon::parse('2026-10-16 00:00', 'UTC'));
        $this->assertSame($this->cells('2026-10-16', '18:00', 12), $plan['cells']);
        $this->assertSame([], $plan['lockedCells']);
    }

    #[Test]
    public function it_locks_a_row_that_is_not_on_a_fifteen_minute_boundary(): void
    {
        $plan = (new AvailabilityPlanner)->seed(['2026-10-16'], collect([$this->row('2026-10-16', '18:05:00', '21:00:00')]), Carbon::parse('2026-10-16 00:00', 'UTC'));
        $this->assertSame([], $plan['cells']);
        $this->assertContains('2026-10-16 18:00', $plan['lockedCells']);
        $this->assertCount(12, $plan['lockedCells']);
    }

    #[Test]
    public function it_locks_a_row_shorter_than_the_minimum(): void
    {
        $plan = (new AvailabilityPlanner)->seed(['2026-10-16'], collect([$this->row('2026-10-16', '18:00:00', '18:30:00')]), Carbon::parse('2026-10-16 00:00', 'UTC'));
        $this->assertSame([], $plan['cells']);
        $this->assertSame(['2026-10-16 18:00', '2026-10-16 18:15'], $plan['lockedCells']);
    }

    #[Test]
    public function it_locks_a_row_that_started_with_under_the_minimum_left(): void
    {
        $plan = (new AvailabilityPlanner)->seed(['2026-10-16'], collect([$this->row('2026-10-16', '18:00:00', '21:00:00')]), Carbon::parse('2026-10-16 20:30', 'UTC'));
        $this->assertSame([], $plan['cells']);
    }

    #[Test]
    public function it_seeds_only_the_editable_remainder_of_a_partly_past_row(): void
    {
        $plan = (new AvailabilityPlanner)->seed(['2026-10-16'], collect([$this->row('2026-10-16', '18:00:00', '21:00:00')]), Carbon::parse('2026-10-16 18:20', 'UTC'));
        $this->assertSame($this->cells('2026-10-16', '18:30', 10), $plan['cells']);
        $this->assertSame(['2026-10-16 18:00', '2026-10-16 18:15'], $plan['lockedCells']);
    }

    #[Test]
    public function it_locks_the_tail_of_a_cross_midnight_row_on_the_next_date(): void
    {
        $rows = collect([$this->row('2026-10-16', '22:00:00', '02:00:00')]);
        $plan = (new AvailabilityPlanner)->seed(['2026-10-16', '2026-10-17'], $rows, Carbon::parse('2026-10-16 00:00', 'UTC'));
        $this->assertSame([], $plan['cells']);
        $this->assertContains('2026-10-16 22:00', $plan['lockedCells']);
        $this->assertContains('2026-10-17 00:00', $plan['lockedCells']);
        $this->assertContains('2026-10-17 01:45', $plan['lockedCells']);
    }

    #[Test]
    public function it_ignores_rows_outside_the_requested_dates(): void
    {
        $plan = (new AvailabilityPlanner)->seed(['2026-10-16'], collect([$this->row('2026-10-18', '18:00:00', '21:00:00')]), Carbon::parse('2026-10-16 00:00', 'UTC'));
        $this->assertSame([], $plan['cells']);
        $this->assertSame([], $plan['lockedCells']);
    }

    #[Test]
    public function it_reports_an_unpainted_row_as_removed(): void
    {
        $row = $this->row('2026-10-16', '18:00:00', '21:00:00');
        $plan = (new AvailabilityPlanner)->plan(['2026-10-16'], collect([$row]), [], Carbon::parse('2026-10-16 00:00', 'UTC'));
        $this->assertCount(1, $plan->changes);
        $this->assertSame(AvailabilityChangeType::Removed, $plan->changes[0]->type);
        $this->assertSame('2026-10-16 18:00:00', $plan->changes[0]->oldFrom->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_reports_an_untouched_row_as_unchanged(): void
    {
        $row = $this->row('2026-10-16', '18:00:00', '21:00:00');
        $plan = (new AvailabilityPlanner)->plan(['2026-10-16'], collect([$row]), $this->cells('2026-10-16', '18:00', 12), Carbon::parse('2026-10-16 00:00', 'UTC'));
        $this->assertCount(1, $plan->changes);
        $this->assertSame(AvailabilityChangeType::Unchanged, $plan->changes[0]->type);
    }

    #[Test]
    public function it_reports_an_extended_row_as_edited(): void
    {
        $row = $this->row('2026-10-16', '18:00:00', '21:00:00');
        $plan = (new AvailabilityPlanner)->plan(['2026-10-16'], collect([$row]), $this->cells('2026-10-16', '18:00', 14), Carbon::parse('2026-10-16 00:00', 'UTC'));
        $this->assertSame(AvailabilityChangeType::Edited, $plan->changes[0]->type);
        $this->assertSame('2026-10-16 21:30:00', $plan->changes[0]->newTo->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_reports_a_shortened_row_as_edited(): void
    {
        $row = $this->row('2026-10-16', '18:00:00', '21:00:00');
        $plan = (new AvailabilityPlanner)->plan(['2026-10-16'], collect([$row]), $this->cells('2026-10-16', '18:30', 10), Carbon::parse('2026-10-16 00:00', 'UTC'));
        $this->assertSame(AvailabilityChangeType::Edited, $plan->changes[0]->type);
        $this->assertSame('2026-10-16 18:30:00', $plan->changes[0]->newFrom->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_reports_a_holed_row_as_edited_and_added(): void
    {
        $row = $this->row('2026-10-16', '18:00:00', '21:00:00');
        $painted = array_merge($this->cells('2026-10-16', '18:00', 4), $this->cells('2026-10-16', '20:00', 4));
        $plan = (new AvailabilityPlanner)->plan(['2026-10-16'], collect([$row]), $painted, Carbon::parse('2026-10-16 00:00', 'UTC'));
        $this->assertSame([AvailabilityChangeType::Edited, AvailabilityChangeType::Added], array_map(fn ($c) => $c->type, $plan->changes));
        $this->assertSame('2026-10-16 19:00:00', $plan->changes[0]->newTo->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-16 20:00:00', $plan->changes[1]->newFrom->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_merges_two_rows_covered_by_one_painted_range(): void
    {
        $first = $this->row('2026-10-16', '18:00:00', '19:00:00');
        $second = $this->row('2026-10-16', '20:00:00', '21:00:00');
        $plan = (new AvailabilityPlanner)->plan(['2026-10-16'], collect([$first, $second]), $this->cells('2026-10-16', '18:00', 12), Carbon::parse('2026-10-16 00:00', 'UTC'));
        $this->assertCount(1, $plan->changes);
        $this->assertSame(AvailabilityChangeType::Merged, $plan->changes[0]->type);
        $this->assertSame($first, $plan->changes[0]->existing);
        $this->assertSame([$second], $plan->changes[0]->swallowed);
        $this->assertSame('2026-10-16 21:00:00', $plan->changes[0]->newTo->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_merges_abutting_rows_covered_by_one_painted_range(): void
    {
        $first = $this->row('2026-10-16', '18:00:00', '19:00:00');
        $second = $this->row('2026-10-16', '19:00:00', '21:00:00');
        $plan = (new AvailabilityPlanner)->plan(['2026-10-16'], collect([$first, $second]), $this->cells('2026-10-16', '18:00', 12), Carbon::parse('2026-10-16 00:00', 'UTC'));
        $this->assertSame(AvailabilityChangeType::Merged, $plan->changes[0]->type);
        $this->assertSame([$second], $plan->changes[0]->swallowed);
    }

    #[Test]
    public function it_adds_a_painted_range_that_matches_no_row(): void
    {
        $plan = (new AvailabilityPlanner)->plan(['2026-10-16'], collect(), $this->cells('2026-10-16', '18:00', 4), Carbon::parse('2026-10-16 00:00', 'UTC'));
        $this->assertSame(AvailabilityChangeType::Added, $plan->changes[0]->type);
        $this->assertNull($plan->changes[0]->existing);
    }

    #[Test]
    public function it_drops_and_counts_painted_runs_under_the_minimum(): void
    {
        $plan = (new AvailabilityPlanner)->plan(['2026-10-16'], collect(), $this->cells('2026-10-16', '18:00', 2), Carbon::parse('2026-10-16 00:00', 'UTC'));
        $this->assertSame([], $plan->changes);
        $this->assertSame(1, $plan->skippedShortRuns);
    }

    #[Test]
    public function it_keeps_a_run_of_exactly_the_minimum(): void
    {
        $plan = (new AvailabilityPlanner)->plan(['2026-10-16'], collect(), $this->cells('2026-10-16', '18:00', 3), Carbon::parse('2026-10-16 00:00', 'UTC'));
        $this->assertSame(AvailabilityChangeType::Added, $plan->changes[0]->type);
        $this->assertSame(0, $plan->skippedShortRuns);
        $this->assertSame('2026-10-16 18:45:00', $plan->changes[0]->newTo->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_leaves_a_locked_row_out_of_the_plan(): void
    {
        $row = $this->row('2026-10-16', '18:05:00', '21:00:00');
        $plan = (new AvailabilityPlanner)->plan(['2026-10-16'], collect([$row]), [], Carbon::parse('2026-10-16 00:00', 'UTC'));
        $this->assertSame([], $plan->changes);
    }

    #[Test]
    public function it_extends_a_future_row_backwards(): void
    {
        $row = $this->row('2026-10-16', '18:00:00', '21:00:00');
        $plan = (new AvailabilityPlanner)->plan(['2026-10-16'], collect([$row]), $this->cells('2026-10-16', '17:00', 16), Carbon::parse('2026-10-16 00:00', 'UTC'));
        $this->assertSame(AvailabilityChangeType::Edited, $plan->changes[0]->type);
        $this->assertSame('2026-10-16 17:00:00', $plan->changes[0]->newFrom->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-16 21:00:00', $plan->changes[0]->newTo->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_never_moves_a_partly_past_row_start_backwards(): void
    {
        $row = $this->row('2026-10-16', '18:00:00', '21:00:00');
        $now = Carbon::parse('2026-10-16 18:20', 'UTC');
        $untouched = (new AvailabilityPlanner)->plan(['2026-10-16'], collect([$row]), $this->cells('2026-10-16', '18:30', 10), $now);
        $this->assertSame(AvailabilityChangeType::Unchanged, $untouched->changes[0]->type);

        $shortened = (new AvailabilityPlanner)->plan(['2026-10-16'], collect([$row]), $this->cells('2026-10-16', '18:30', 6), $now);
        $this->assertSame(AvailabilityChangeType::Edited, $shortened->changes[0]->type);
        $this->assertSame('2026-10-16 18:00:00', $shortened->changes[0]->newFrom->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-16 20:00:00', $shortened->changes[0]->newTo->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_reports_a_started_row_as_unchanged_when_the_result_equals_its_stored_bounds(): void
    {
        $row = $this->row('2026-10-16', '16:00:00', '21:00:00');
        $plan = (new AvailabilityPlanner)->plan(['2026-10-16'], collect([$row]), $this->cells('2026-10-16', '17:30', 14), Carbon::parse('2026-10-16 17:20', 'UTC'));
        $this->assertCount(1, $plan->changes);
        $this->assertSame(AvailabilityChangeType::Unchanged, $plan->changes[0]->type);
    }

    #[Test]
    public function it_reports_a_started_row_as_unchanged_when_an_erased_leading_cell_leaves_its_stored_bounds(): void
    {
        $row = $this->row('2026-10-16', '16:00:00', '21:00:00');
        $plan = (new AvailabilityPlanner)->plan(['2026-10-16'], collect([$row]), $this->cells('2026-10-16', '17:15', 15), Carbon::parse('2026-10-16 16:50', 'UTC'));
        $this->assertCount(1, $plan->changes);
        $this->assertSame(AvailabilityChangeType::Unchanged, $plan->changes[0]->type);
    }

    #[Test]
    public function it_merges_two_rows_when_the_painted_range_starts_before_them(): void
    {
        $first = $this->row('2026-10-16', '18:00:00', '19:00:00');
        $second = $this->row('2026-10-16', '20:00:00', '21:00:00');
        $plan = (new AvailabilityPlanner)->plan(['2026-10-16'], collect([$first, $second]), $this->cells('2026-10-16', '17:00', 16), Carbon::parse('2026-10-16 00:00', 'UTC'));
        $this->assertSame(AvailabilityChangeType::Merged, $plan->changes[0]->type);
        $this->assertSame('2026-10-16 17:00:00', $plan->changes[0]->newFrom->format('Y-m-d H:i:s'));
        $this->assertSame([$second], $plan->changes[0]->swallowed);
    }

    #[Test]
    public function it_treats_a_midnight_ending_row_as_unchanged(): void
    {
        $row = $this->row('2026-10-16', '22:00:00', '00:00:00');
        $plan = (new AvailabilityPlanner)->plan(['2026-10-16'], collect([$row]), $this->cells('2026-10-16', '22:00', 8), Carbon::parse('2026-10-16 00:00', 'UTC'));
        $this->assertSame(AvailabilityChangeType::Unchanged, $plan->changes[0]->type);
    }

    #[Test]
    public function it_plans_each_date_independently(): void
    {
        $rows = collect([$this->row('2026-10-16', '18:00:00', '21:00:00'), $this->row('2026-10-17', '18:00:00', '21:00:00')]);
        $plan = (new AvailabilityPlanner)->plan(['2026-10-16', '2026-10-17'], $rows, $this->cells('2026-10-16', '18:00', 12), Carbon::parse('2026-10-16 00:00', 'UTC'));
        $this->assertSame([AvailabilityChangeType::Unchanged, AvailabilityChangeType::Removed], array_map(fn ($c) => $c->type, $plan->changes));
    }

    #[Test]
    public function it_writes_nothing_when_nothing_is_painted_on_any_visited_date(): void
    {
        $plan = (new AvailabilityPlanner)->plan(['2026-10-16', '2026-10-17'], collect(), [], Carbon::parse('2026-10-16 00:00', 'UTC'));
        $this->assertSame([], $plan->changes);
        $this->assertSame(0, $plan->skippedShortRuns);
    }

    #[Test]
    public function it_never_removes_a_row_that_was_not_seeded_into_the_client_state(): void
    {
        $seeded = $this->row('2026-10-16', '18:00:00', '21:00:00');
        $seeded->id = 1;

        $late = $this->row('2026-10-16', '22:00:00', '23:00:00');
        $late->id = 2;

        $plan = (new AvailabilityPlanner)->plan(
            ['2026-10-16'],
            collect([$seeded, $late]),
            $this->cells('2026-10-16', '18:00', 12),
            Carbon::parse('2026-10-16 00:00', 'UTC'),
            [1],
        );

        $this->assertCount(1, $plan->changes);
        $this->assertSame(AvailabilityChangeType::Unchanged, $plan->changes[0]->type);
        $this->assertSame($seeded, $plan->changes[0]->existing);
    }

    #[Test]
    public function it_makes_no_change_for_a_cross_midnight_row_with_no_paint(): void
    {
        $row = $this->row('2026-10-16', '22:00:00', '02:00:00');

        $plan = (new AvailabilityPlanner)->plan(
            ['2026-10-16', '2026-10-17'],
            collect([$row]),
            [],
            Carbon::parse('2026-10-16 00:00', 'UTC'),
        );

        $this->assertSame([], $plan->changes);
    }
}
