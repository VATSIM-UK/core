<?php

namespace Tests\Feature\Admin\Account\RelationManagers;

use App\Filament\Admin\Resources\Accounts\RelationManagers\RosterHistoryRelationManager;
use App\Models\Mship\Account;
use App\Models\NetworkData\Atc;
use App\Models\RosterHistory;
use App\Models\RosterUpdate;
use Carbon\Carbon;
use Filament\Resources\Pages\ViewRecord;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class RosterHistoryRelationManagerTest extends BaseAdminTestCase
{
    private Account $account;

    private Account $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()->create();
        $this->admin = Account::factory()->create();
    }

    public function test_it_renders()
    {
        $this->actingAsSuperUser();

        Livewire::test(RosterHistoryRelationManager::class, [
            'ownerRecord' => $this->account,
            'pageClass' => ViewRecord::class,
        ])
            ->assertSuccessful();
    }

    public function test_it_displays_roster_history_records()
    {
        $this->actingAsSuperUser();

        $history = RosterHistory::create([
            'account_id' => $this->account->id,
            'original_created_at' => Carbon::parse('2024-01-15 10:00:00'),
            'original_updated_at' => Carbon::parse('2024-06-01 12:00:00'),
            'removed_by' => $this->admin->id,
            'created_at' => Carbon::parse('2024-12-20 14:00:00'),
        ]);

        Livewire::test(RosterHistoryRelationManager::class, [
            'ownerRecord' => $this->account,
            'pageClass' => ViewRecord::class,
        ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$history]);
    }

    public function test_it_displays_controlling_hours()
    {
        $this->actingAsSuperUser();

        $history = RosterHistory::create([
            'account_id' => $this->account->id,
            'original_created_at' => Carbon::parse('2024-01-01 00:00:00'),
            'original_updated_at' => Carbon::parse('2024-01-01 00:00:00'),
            'removed_by' => null,
            'created_at' => Carbon::parse('2024-12-31 23:59:59'),
        ]);

        Atc::create([
            'account_id' => $this->account->id,
            'callsign' => 'EGLL_N_APP',
            'connected_at' => Carbon::parse('2024-06-01 10:00:00'),
            'disconnected_at' => Carbon::parse('2024-06-01 12:30:00'),
            'minutes_online' => 150,
            'qualification_id' => 1,
        ]);

        Livewire::test(RosterHistoryRelationManager::class, [
            'ownerRecord' => $this->account,
            'pageClass' => ViewRecord::class,
        ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$history]);
    }

    public function test_it_excludes_sessions_outside_roster_period()
    {
        $this->actingAsSuperUser();

        $history = RosterHistory::create([
            'account_id' => $this->account->id,
            'original_created_at' => Carbon::parse('2024-06-01 00:00:00'),
            'original_updated_at' => Carbon::parse('2024-06-01 00:00:00'),
            'removed_by' => null,
            'created_at' => Carbon::parse('2024-08-01 00:00:00'),
        ]);

        Atc::create([
            'account_id' => $this->account->id,
            'callsign' => 'EGLL_N_APP',
            'connected_at' => Carbon::parse('2024-09-01 10:00:00'),
            'disconnected_at' => Carbon::parse('2024-09-01 11:00:00'),
            'minutes_online' => 60,
            'qualification_id' => 1,
        ]);

        Livewire::test(RosterHistoryRelationManager::class, [
            'ownerRecord' => $this->account,
            'pageClass' => ViewRecord::class,
        ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$history]);
    }

    public function test_it_displays_roster_update_period()
    {
        $this->actingAsSuperUser();

        $rosterUpdate = RosterUpdate::create([
            'period_start' => Carbon::parse('2024-12-15'),
            'period_end' => Carbon::parse('2024-12-22'),
            'data' => ['test' => true],
        ]);

        $history = RosterHistory::create([
            'account_id' => $this->account->id,
            'original_created_at' => Carbon::parse('2024-01-15 10:00:00'),
            'original_updated_at' => Carbon::parse('2024-06-01 12:00:00'),
            'removed_by' => $this->admin->id,
            'roster_update_id' => $rosterUpdate->id,
            'created_at' => Carbon::parse('2024-12-20 14:00:00'),
        ]);

        Livewire::test(RosterHistoryRelationManager::class, [
            'ownerRecord' => $this->account,
            'pageClass' => ViewRecord::class,
        ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$history]);
    }
}
