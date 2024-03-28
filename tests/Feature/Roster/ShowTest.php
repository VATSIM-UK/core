<?php

namespace Tests\Feature\Roster;

use App\Livewire\Roster\Show;
use App\Models\Atc\Position;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use App\Models\Roster;
use Livewire;
use Tests\TestCase;

class ShowTest extends TestCase
{
    public function test_notification_shown_when_position_not_found_in_search()
    {
        $account = Account::factory()->create();
        $qualification = Qualification::code('S2')->first();
        $account->addState(\App\Models\Mship\State::findByCode('DIVISION'));
        $account->addQualification($qualification);

        Roster::create([
            'account_id' => $account->id,
        ]);

        Livewire::test(Show::class, ['account' => $account])
            ->set('searchTerm', 'ZZZ')
            ->call('search')
            ->assertSessionHas('filament.notifications', function ($value) {
                return $value[0]['title'] == 'Position cannot be found.' && $value[0]['status'] == 'danger';
            })
            // ensure the position is cleared from any previous searches
            ->assertSet('position', null)
            ->assertSet('searchTerm', null);
    }

    public function test_shows_position_as_controllable_when_searched_and_account_can_control()
    {
        $account = Account::factory()->create();
        // S3 qualification for this home member would make any APP position controllable
        $qualification = Qualification::code('S3')->first();
        $account->addState(State::findByCode('DIVISION'));
        $account->addQualification($qualification);

        Roster::create([
            'account_id' => $account->id,
        ]);

        $position = Position::factory()->create([
            'callsign' => 'EGKK_APP',
            'type' => Position::TYPE_APPROACH,
        ]);

        Livewire::test(Show::class, ['account' => $account])
            ->set('searchTerm', 'EGKK_APP')
            ->call('search')
            ->assertSee("{$account->id} can control {$position->callsign}");
    }

    public function test_shows_position_as_not_controllable_when_search_and_account_cannot_control()
    {
        $account = Account::factory()->create();
        // S2 qualification for this home member would not make any APP position controllable
        $qualification = Qualification::code('S2')->first();
        $account->addState(State::findByCode('DIVISION'));
        $account->addQualification($qualification);

        Roster::create([
            'account_id' => $account->id,
            'type' => Position::TYPE_APPROACH,
        ]);

        $position = Position::factory()->create([
            'callsign' => 'EGKK_APP',
            'type' => Position::TYPE_APPROACH,
        ]);

        Livewire::test(Show::class, ['account' => $account])
            ->set('searchTerm', 'EGKK_APP')
            ->call('search')
            ->assertSee("{$account->id} cannot control {$position->callsign}");
    }

    public function test_clears_position_and_can_control_when_new_search()
    {
        $account = Account::factory()->create();
        $qualification = Qualification::code('S2')->first();
        $account->addState(State::findByCode('DIVISION'));
        $account->addQualification($qualification);

        Roster::create([
            'account_id' => $account->id,
        ]);

        $controllablePosition = Position::factory()->create([
            'callsign' => 'EGKK_TWR',
            'type' => Position::TYPE_TOWER,
        ]);

        $uncontrollablePosition = Position::factory()->create([
            'callsign' => 'EGKK_APP',
            'type' => Position::TYPE_APPROACH,
        ]);

        Livewire::test(Show::class, ['account' => $account])
            ->set('searchTerm', 'EGKK_TWR')
            ->call('search')
            ->assertSee("{$account->id} can control {$controllablePosition->callsign}")
            ->set('searchTerm', 'EGKK_APP')
            ->call('search')
            ->assertDontSee("{$account->id} can control {$uncontrollablePosition->callsign}")
            ->set('searchTerm', 'EGKK_DEP')
            ->call('search')
            ->assertSessionHas('filament.notifications', function ($value) {
                return $value[0]['title'] == 'Position cannot be found.' && $value[0]['status'] == 'danger';
            })
            ->assertDontSee("{$account->id} can control {$uncontrollablePosition->callsign}")
            ->assertSet('position', null)
            ->assertSet('searchTerm', null);
    }
}
