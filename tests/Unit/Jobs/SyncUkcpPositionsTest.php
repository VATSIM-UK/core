<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\SyncUkcpPositions;
use App\Libraries\UKCP;
use App\Models\Atc\Position;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SyncUkcpPositionsTest extends TestCase
{
    use DatabaseTransactions;

    private function buildUkcpPosition(int $id, string $callsign, float $frequency, ?string $description = null): object
    {
        return (object) [
            'id' => $id,
            'callsign' => $callsign,
            'frequency' => $frequency,
            'description' => $description,
        ];
    }

    private function buildUkcpV2Position(int $id, string $callsign, float $frequency, array $topDown): object
    {
        return (object) [
            'id' => $id,
            'callsign' => $callsign,
            'frequency' => $frequency,
            'top_down' => $topDown,
        ];
    }

    #[Test]
    public function it_creates_new_positions_from_ukcp_data(): void
    {
        $ukcpPositions = new Collection([
            $this->buildUkcpPosition(1, 'EGBB_APP', 123.950, 'Birmingham Approach'),
        ]);

        $this->mock(UKCP::class, function (MockInterface $mock) use ($ukcpPositions) {
            $mock->shouldReceive('getAllControllerPositions')
                ->once()
                ->andReturn($ukcpPositions);

            $mock->shouldReceive('getControllerPositionsV2Dependency')
                ->once()
                ->andReturn(new Collection);
        });

        (new SyncUkcpPositions)->handle(app(UKCP::class));

        $this->assertDatabaseHas('positions', [
            'callsign' => 'EGBB_APP',
            'frequency' => 123.950,
            'name' => 'Birmingham Approach',
            'type' => Position::TYPE_APPROACH,
            'ukcp_position_id' => 1,
            'virtual' => false,
            'temporarily_endorsable' => false,
        ]);
    }

    #[Test]
    public function it_uses_callsign_as_fallback_name_when_description_is_null(): void
    {
        $ukcpPositions = new Collection([
            $this->buildUkcpPosition(2, 'EGPH_TWR', 118.700, null),
        ]);

        $this->mock(UKCP::class, function (MockInterface $mock) use ($ukcpPositions) {
            $mock->shouldReceive('getAllControllerPositions')
                ->once()
                ->andReturn($ukcpPositions);

            $mock->shouldReceive('getControllerPositionsV2Dependency')
                ->once()
                ->andReturn(new Collection);
        });

        (new SyncUkcpPositions)->handle(app(UKCP::class));

        $this->assertDatabaseHas('positions', [
            'callsign' => 'EGPH_TWR',
            'name' => 'EGPH_TWR',
        ]);
    }

    #[Test]
    public function it_infers_type_from_callsign_suffix(): void
    {
        $positions = new Collection([
            $this->buildUkcpPosition(1, 'EGBB_APP', 123.950),
            $this->buildUkcpPosition(2, 'EGBB_DEL', 121.920),
            $this->buildUkcpPosition(3, 'EGPH_TWR', 118.700),
            $this->buildUkcpPosition(4, 'EGPH_GND', 121.750),
            $this->buildUkcpPosition(5, 'LON_C_CTR', 127.100),
            $this->buildUkcpPosition(6, 'EGBB_ATIS', 136.025),
        ]);

        $this->mock(UKCP::class, function (MockInterface $mock) use ($positions) {
            $mock->shouldReceive('getAllControllerPositions')
                ->once()
                ->andReturn($positions);

            $mock->shouldReceive('getControllerPositionsV2Dependency')
                ->once()
                ->andReturn(new Collection);
        });

        (new SyncUkcpPositions)->handle(app(UKCP::class));

        $this->assertDatabaseHas('positions', ['callsign' => 'EGBB_APP', 'type' => Position::TYPE_APPROACH]);
        $this->assertDatabaseHas('positions', ['callsign' => 'EGBB_DEL', 'type' => Position::TYPE_DELIVERY]);
        $this->assertDatabaseHas('positions', ['callsign' => 'EGPH_TWR', 'type' => Position::TYPE_TOWER]);
        $this->assertDatabaseHas('positions', ['callsign' => 'EGPH_GND', 'type' => Position::TYPE_GROUND]);
        $this->assertDatabaseHas('positions', ['callsign' => 'LON_C_CTR', 'type' => Position::TYPE_ENROUTE]);
        $this->assertDatabaseHas('positions', ['callsign' => 'EGBB_ATIS', 'type' => Position::TYPE_ATIS]);
    }

    #[Test]
    public function it_updates_existing_positions_frequency_and_ukcp_id(): void
    {
        $position = Position::factory()->create([
            'callsign' => 'EGBB_APP',
            'frequency' => 123.950,
            'ukcp_position_id' => null,
            'name' => 'Birmingham Approach',
            'type' => Position::TYPE_APPROACH,
        ]);

        $ukcpPositions = new Collection([
            $this->buildUkcpPosition(42, 'EGBB_APP', 123.980),
        ]);

        $this->mock(UKCP::class, function (MockInterface $mock) use ($ukcpPositions) {
            $mock->shouldReceive('getAllControllerPositions')
                ->once()
                ->andReturn($ukcpPositions);

            $mock->shouldReceive('getControllerPositionsV2Dependency')
                ->once()
                ->andReturn(new Collection);
        });

        (new SyncUkcpPositions)->handle(app(UKCP::class));

        $position->refresh();

        // Frequency and ukcp_position_id updated
        $this->assertEquals(123.980, $position->frequency);
        $this->assertEquals(42, $position->ukcp_position_id);

        // Core-only fields preserved (type accessor returns string label)
        $this->assertEquals('Birmingham Approach', $position->name);
        $this->assertEquals('Approach/Radar', $position->type);
    }

    #[Test]
    public function it_does_not_update_preserved_fields_on_existing_positions(): void
    {
        $position = Position::factory()->create([
            'callsign' => 'EGPH_TWR',
            'name' => 'Custom Name',
            'type' => Position::TYPE_TOWER,
            'temporarily_endorsable' => true,
            'virtual' => true,
        ]);

        $ukcpPositions = new Collection([
            $this->buildUkcpPosition(1, 'EGPH_TWR', 118.705, 'UKCP Description'),
        ]);

        $this->mock(UKCP::class, function (MockInterface $mock) use ($ukcpPositions) {
            $mock->shouldReceive('getAllControllerPositions')
                ->once()
                ->andReturn($ukcpPositions);

            $mock->shouldReceive('getControllerPositionsV2Dependency')
                ->once()
                ->andReturn(new Collection);
        });

        (new SyncUkcpPositions)->handle(app(UKCP::class));

        $position->refresh();

        $this->assertEquals('Custom Name', $position->name);
        $this->assertEquals('Tower', $position->type);
        $this->assertEquals(1, $position->temporarily_endorsable);
        $this->assertTrue($position->virtual);
    }

    #[Test]
    public function it_skips_update_when_nothing_changed(): void
    {
        $position = Position::factory()->create([
            'callsign' => 'EGBB_APP',
            'frequency' => 123.950,
            'ukcp_position_id' => 5,
        ]);

        $ukcpPositions = new Collection([
            $this->buildUkcpPosition(5, 'EGBB_APP', 123.950),
        ]);

        $this->mock(UKCP::class, function (MockInterface $mock) use ($ukcpPositions) {
            $mock->shouldReceive('getAllControllerPositions')
                ->once()
                ->andReturn($ukcpPositions);

            $mock->shouldReceive('getControllerPositionsV2Dependency')
                ->once()
                ->andReturn(new Collection);
        });

        // Capture the updated_at before the sync
        $originalUpdatedAt = $position->updated_at;

        (new SyncUkcpPositions)->handle(app(UKCP::class));

        $position->refresh();

        // Timestamps should not have changed since no columns were updated
        $this->assertEquals($originalUpdatedAt->timestamp, $position->updated_at->timestamp);
    }

    #[Test]
    public function it_soft_deletes_synced_positions_removed_from_ukcp(): void
    {
        $toRemove = Position::factory()->create([
            'callsign' => 'EGBB_APP',
            'ukcp_position_id' => 1,
            'virtual' => false,
        ]);

        $toKeep = Position::factory()->create([
            'callsign' => 'EGPH_TWR',
            'ukcp_position_id' => 2,
            'virtual' => false,
        ]);

        $ukcpPositions = new Collection([
            $this->buildUkcpPosition(2, 'EGPH_TWR', 118.700),
        ]);

        $this->mock(UKCP::class, function (MockInterface $mock) use ($ukcpPositions) {
            $mock->shouldReceive('getAllControllerPositions')
                ->once()
                ->andReturn($ukcpPositions);

            $mock->shouldReceive('getControllerPositionsV2Dependency')
                ->once()
                ->andReturn(new Collection);
        });

        (new SyncUkcpPositions)->handle(app(UKCP::class));

        // Removed position is soft-deleted
        $this->assertSoftDeleted($toRemove);

        // Kept position is still active
        $this->assertNotSoftDeleted($toKeep);
    }

    #[Test]
    public function it_nulls_ukcp_position_id_on_soft_deleted_positions(): void
    {
        $toRemove = Position::factory()->create([
            'callsign' => 'EGGP_APP',
            'ukcp_position_id' => 1,
            'virtual' => false,
        ]);

        $ukcpPositions = new Collection([
            $this->buildUkcpPosition(2, 'EGPH_TWR', 118.700),
        ]);

        $this->mock(UKCP::class, function (MockInterface $mock) use ($ukcpPositions) {
            $mock->shouldReceive('getAllControllerPositions')
                ->once()
                ->andReturn($ukcpPositions);

            $mock->shouldReceive('getControllerPositionsV2Dependency')
                ->once()
                ->andReturn(new Collection);
        });

        (new SyncUkcpPositions)->handle(app(UKCP::class));

        $this->assertSoftDeleted($toRemove);
        $this->assertNull($toRemove->refresh()->ukcp_position_id);
    }

    #[Test]
    public function it_allows_ukcp_id_reuse_after_soft_delete(): void
    {
        // Simulate: UKCP had position 1 (EGBB_APP), removed from feed, then re-added as a different position
        Position::factory()->create([
            'callsign' => 'EGBB_APP',
            'ukcp_position_id' => 1,
            'virtual' => false,
        ]);

        $ukcpPositions = new Collection([
            $this->buildUkcpPosition(2, 'EGPH_TWR', 118.700),
        ]);

        $this->mock(UKCP::class, function (MockInterface $mock) use ($ukcpPositions) {
            $mock->shouldReceive('getAllControllerPositions')
                ->once()
                ->andReturn($ukcpPositions);

            $mock->shouldReceive('getControllerPositionsV2Dependency')
                ->once()
                ->andReturn(new Collection);
        });

        (new SyncUkcpPositions)->handle(app(UKCP::class));

        // Position 1 is soft-deleted with its UKCP ID nullified
        $this->assertDatabaseHas('positions', [
            'callsign' => 'EGBB_APP',
            'ukcp_position_id' => null,
        ]);

        // Now UKCP reuses ID 1 for a completely new position
        $ukcpPositions = new Collection([
            $this->buildUkcpPosition(1, 'EGKK_TWR', 118.975),
            $this->buildUkcpPosition(2, 'EGPH_TWR', 118.700),
        ]);

        $this->mock(UKCP::class, function (MockInterface $mock) use ($ukcpPositions) {
            $mock->shouldReceive('getAllControllerPositions')
                ->once()
                ->andReturn($ukcpPositions);

            $mock->shouldReceive('getControllerPositionsV2Dependency')
                ->once()
                ->andReturn(new Collection);
        });

        (new SyncUkcpPositions)->handle(app(UKCP::class));

        // The reused ID 1 should have been created without constraint violation
        $this->assertDatabaseHas('positions', [
            'callsign' => 'EGKK_TWR',
            'ukcp_position_id' => 1,
            'deleted_at' => null,
        ]);
    }

    #[Test]
    public function it_does_not_soft_delete_core_native_positions(): void
    {
        $coreNative = Position::factory()->create([
            'callsign' => 'EGXX_ATIS',
            'ukcp_position_id' => null,
        ]);

        $ukcpPositions = new Collection([
            $this->buildUkcpPosition(1, 'EGLL_S_TWR', 118.500),
        ]);

        $this->mock(UKCP::class, function (MockInterface $mock) use ($ukcpPositions) {
            $mock->shouldReceive('getAllControllerPositions')
                ->once()
                ->andReturn($ukcpPositions);

            $mock->shouldReceive('getControllerPositionsV2Dependency')
                ->once()
                ->andReturn(new Collection);
        });

        (new SyncUkcpPositions)->handle(app(UKCP::class));

        // Core-native positions (no ukcp_position_id) are never touched
        $this->assertNotSoftDeleted($coreNative);
    }

    #[Test]
    public function dry_run_mode_makes_no_database_changes(): void
    {
        Position::factory()->create([
            'callsign' => 'EGBB_APP',
            'frequency' => 123.950,
            'ukcp_position_id' => null,
        ]);

        $ukcpPositions = new Collection([
            $this->buildUkcpPosition(1, 'EGBB_APP', 123.980),
            $this->buildUkcpPosition(2, 'EGGP_APP', 119.850),
        ]);

        $this->mock(UKCP::class, function (MockInterface $mock) use ($ukcpPositions) {
            $mock->shouldReceive('getAllControllerPositions')
                ->once()
                ->andReturn($ukcpPositions);

            $mock->shouldReceive('getControllerPositionsV2Dependency')
                ->once()
                ->andReturn(new Collection);
        });

        (new SyncUkcpPositions(dryRun: true))->handle(app(UKCP::class));

        // EGBB_APP frequency NOT updated
        $this->assertDatabaseHas('positions', [
            'callsign' => 'EGBB_APP',
            'frequency' => 123.950,
        ]);

        // EGGP_APP NOT created
        $this->assertDatabaseMissing('positions', [
            'callsign' => 'EGGP_APP',
        ]);
    }

    #[Test]
    public function it_updates_callsign_when_ukcp_renames_a_position(): void
    {
        // Core has position linked to UKCP id 1
        Position::factory()->create([
            'callsign' => 'EGBB_APP',
            'ukcp_position_id' => 1,
            'name' => 'Birmingham Approach',
            'type' => Position::TYPE_APPROACH,
        ]);

        // UKCP renames it but keeps id 1
        $ukcpPositions = new Collection([
            $this->buildUkcpPosition(1, 'EGBB_APP_2', 123.950),
        ]);

        $this->mock(UKCP::class, function (MockInterface $mock) use ($ukcpPositions) {
            $mock->shouldReceive('getAllControllerPositions')
                ->once()
                ->andReturn($ukcpPositions);

            $mock->shouldReceive('getControllerPositionsV2Dependency')
                ->once()
                ->andReturn(new Collection);
        });

        (new SyncUkcpPositions)->handle(app(UKCP::class));

        // Old callsign was updated to the new one (not soft-deleted)
        $this->assertDatabaseMissing('positions', [
            'callsign' => 'EGBB_APP',
            'deleted_at' => null,
        ]);

        // New callsign exists with same UKCP id and preserved metadata
        $this->assertDatabaseHas('positions', [
            'callsign' => 'EGBB_APP_2',
            'ukcp_position_id' => 1,
            'name' => 'Birmingham Approach',
        ]);
    }

    #[Test]
    public function it_handles_empty_api_response_gracefully(): void
    {
        Position::factory()->create([
            'callsign' => 'EGBB_APP',
            'ukcp_position_id' => 1,
        ]);

        $this->mock(UKCP::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAllControllerPositions')
                ->once()
                ->andReturn(new Collection);
        });

        // Should not throw, and should not delete anything
        (new SyncUkcpPositions)->handle(app(UKCP::class));

        $this->assertDatabaseHas('positions', [
            'callsign' => 'EGBB_APP',
            'deleted_at' => null,
        ]);
    }

    #[Test]
    public function it_populates_top_down_from_v2_endpoint(): void
    {
        $position = Position::factory()->create([
            'callsign' => 'LON_S_CTR',
            'ukcp_position_id' => 1,
        ]);

        $ukcpPositions = new Collection([
            $this->buildUkcpPosition(1, 'LON_S_CTR', 129.425),
        ]);

        $v2Positions = new Collection([
            $this->buildUkcpV2Position(1, 'LON_S_CTR', 129.425, ['EGLL', 'EGKK', 'EGLF']),
        ]);

        $this->mock(UKCP::class, function (MockInterface $mock) use ($ukcpPositions, $v2Positions) {
            $mock->shouldReceive('getAllControllerPositions')
                ->once()
                ->andReturn($ukcpPositions);

            $mock->shouldReceive('getControllerPositionsV2Dependency')
                ->once()
                ->andReturn($v2Positions);
        });

        (new SyncUkcpPositions)->handle(app(UKCP::class));

        $position->refresh();
        $this->assertEquals(['EGLL', 'EGKK', 'EGLF'], $position->top_down);
    }

    #[Test]
    public function it_leaves_top_down_null_for_position_not_in_v2(): void
    {
        $position = Position::factory()->create([
            'callsign' => 'EGLL_TWR',
            'ukcp_position_id' => 1,
        ]);

        $ukcpPositions = new Collection([
            $this->buildUkcpPosition(1, 'EGLL_TWR', 118.500),
        ]);

        $v2Positions = new Collection([]);

        $this->mock(UKCP::class, function (MockInterface $mock) use ($ukcpPositions, $v2Positions) {
            $mock->shouldReceive('getAllControllerPositions')
                ->once()
                ->andReturn($ukcpPositions);

            $mock->shouldReceive('getControllerPositionsV2Dependency')
                ->once()
                ->andReturn($v2Positions);
        });

        (new SyncUkcpPositions)->handle(app(UKCP::class));

        $position->refresh();
        $this->assertNull($position->top_down);
    }

    #[Test]
    public function it_sets_top_down_to_empty_array_when_v2_returns_empty_array(): void
    {
        $position = Position::factory()->create([
            'callsign' => 'EGLL_TWR',
            'ukcp_position_id' => 1,
        ]);

        $ukcpPositions = new Collection([
            $this->buildUkcpPosition(1, 'EGLL_TWR', 118.500),
        ]);

        $v2Positions = new Collection([
            $this->buildUkcpV2Position(1, 'EGLL_TWR', 118.500, []),
        ]);

        $this->mock(UKCP::class, function (MockInterface $mock) use ($ukcpPositions, $v2Positions) {
            $mock->shouldReceive('getAllControllerPositions')
                ->once()
                ->andReturn($ukcpPositions);

            $mock->shouldReceive('getControllerPositionsV2Dependency')
                ->once()
                ->andReturn($v2Positions);
        });

        (new SyncUkcpPositions)->handle(app(UKCP::class));

        $position->refresh();
        $this->assertEquals([], $position->top_down);
    }

    #[Test]
    public function it_updates_top_down_when_v2_data_changes(): void
    {
        $position = Position::factory()->create([
            'callsign' => 'LON_S_CTR',
            'ukcp_position_id' => 1,
            'top_down' => ['EGLL'],
        ]);

        $ukcpPositions = new Collection([
            $this->buildUkcpPosition(1, 'LON_S_CTR', 129.425),
        ]);

        $v2Positions = new Collection([
            $this->buildUkcpV2Position(1, 'LON_S_CTR', 129.425, ['EGLL', 'EGKK', 'EGLF']),
        ]);

        $this->mock(UKCP::class, function (MockInterface $mock) use ($ukcpPositions, $v2Positions) {
            $mock->shouldReceive('getAllControllerPositions')
                ->once()
                ->andReturn($ukcpPositions);

            $mock->shouldReceive('getControllerPositionsV2Dependency')
                ->once()
                ->andReturn($v2Positions);
        });

        (new SyncUkcpPositions)->handle(app(UKCP::class));

        $position->refresh();
        $this->assertEquals(['EGLL', 'EGKK', 'EGLF'], $position->top_down);
    }

    #[Test]
    public function dry_run_mode_does_not_update_top_down(): void
    {
        $position = Position::factory()->create([
            'callsign' => 'LON_S_CTR',
            'ukcp_position_id' => 1,
        ]);

        $ukcpPositions = new Collection([
            $this->buildUkcpPosition(1, 'LON_S_CTR', 129.425),
        ]);

        $v2Positions = new Collection([
            $this->buildUkcpV2Position(1, 'LON_S_CTR', 129.425, ['EGLL', 'EGKK']),
        ]);

        $this->mock(UKCP::class, function (MockInterface $mock) use ($ukcpPositions, $v2Positions) {
            $mock->shouldReceive('getAllControllerPositions')
                ->once()
                ->andReturn($ukcpPositions);

            $mock->shouldReceive('getControllerPositionsV2Dependency')
                ->once()
                ->andReturn($v2Positions);
        });

        (new SyncUkcpPositions(dryRun: true))->handle(app(UKCP::class));

        $position->refresh();
        $this->assertNull($position->top_down);
    }

    #[Test]
    public function it_handles_v2_endpoint_returning_empty_collection(): void
    {
        $position = Position::factory()->create([
            'callsign' => 'EGLL_TWR',
            'ukcp_position_id' => 1,
        ]);

        $ukcpPositions = new Collection([
            $this->buildUkcpPosition(1, 'EGLL_TWR', 118.500),
        ]);

        $this->mock(UKCP::class, function (MockInterface $mock) use ($ukcpPositions) {
            $mock->shouldReceive('getAllControllerPositions')
                ->once()
                ->andReturn($ukcpPositions);

            $mock->shouldReceive('getControllerPositionsV2Dependency')
                ->once()
                ->andReturn(new Collection);
        });

        (new SyncUkcpPositions)->handle(app(UKCP::class));

        $position->refresh();
        $this->assertNull($position->top_down);
    }
}
