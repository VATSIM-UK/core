<?php

namespace Tests\Feature\Admin\WaitingLists;

use App\Filament\Resources\WaitingListResource\Pages\ViewWaitingList;
use App\Filament\Resources\WaitingListResource\RelationManagers\AccountsRelationManager;
use App\Models\Atc\Endorsement;
use App\Models\Mship\Account;
use App\Models\Mship\State;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListFlag;
use App\Rules\HomeMemberId;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class ViewWaitingListPageTest extends BaseAdminTestCase
{
    use DatabaseTransactions;

    public function test_two_relation_manager_tables_are_present()
    {
        $waitingList = factory(WaitingList::class)->create(['department' => 'atc']);
        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewWaitingList::class, ['record' => $waitingList->id])
            ->assertSee('Eligible Accounts')
            ->assertSee('Ineligible Accounts');
    }

    public function test_admin_user_cant_add_student_without_permission()
    {
        $waitingList = factory(WaitingList::class)->create(['department' => 'atc']);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewWaitingList::class, ['record' => $waitingList->id])
            ->assertDontSee('Add student');
    }

    public function test_home_student_can_be_added()
    {
        $waitingList = factory(WaitingList::class)->create(['department' => 'atc']);
        $accountToAdd = Account::factory()->create();
        $accountToAdd->addState(State::findByCode('DIVISION'));

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');
        $this->adminUser->givePermissionTo('waiting-lists.add-accounts.*');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewWaitingList::class, ['record' => $waitingList->id])
            ->callPageAction('add_student', data: [
                'account_id' => $accountToAdd->id,
            ]);

        $this->assertContains($accountToAdd->id, $waitingList->fresh()->accounts->pluck('id'));

        $this->assertDatabaseHas('training_waiting_list_account', [
            'list_id' => $waitingList->id,
            'account_id' => $accountToAdd->id,
        ]);
    }

    public function test_student_cant_be_added_twice()
    {
        Livewire::actingAs($this->adminUser);

        $waitingList = factory(WaitingList::class)->create(['department' => 'atc']);
        $accountToAdd = Account::factory()->create();
        $accountToAdd->addState(State::findByCode('DIVISION'));

        $waitingList->addToWaitingList($accountToAdd, $this->adminUser);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');
        $this->adminUser->givePermissionTo('waiting-lists.add-accounts.*');

        Livewire::test(ViewWaitingList::class, ['record' => $waitingList->id])
            ->callPageAction('add_student', data: [
                'account_id' => $accountToAdd->id,
            ])
            ->assertHasPageActionErrors(['account_id']);
    }

    public function test_non_home_student_cant_be_added()
    {
        $waitingList = factory(WaitingList::class)->create(['department' => 'atc']);
        $accountToAdd = Account::factory()->create();
        $accountToAdd->addState(State::findByCode('INTERNATIONAL'));

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');
        $this->adminUser->givePermissionTo('waiting-lists.add-accounts.*');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewWaitingList::class, ['record' => $waitingList->id])
            ->callPageAction('add_student', data: [
                'account_id' => $accountToAdd->id,
            ])
            ->assertHasPageActionErrors(['account_id' => [HomeMemberId::class]]);

        $this->assertNotContains($accountToAdd->id, $waitingList->fresh()->accounts->pluck('id'));

        $this->assertDatabaseMissing('training_waiting_list_account', [
            'list_id' => $waitingList->id,
            'account_id' => $accountToAdd->id,
        ]);
    }

    public function test_cannot_see_join_date_field_without_permission()
    {
        $waitingList = factory(WaitingList::class)->create(['department' => 'atc']);
        $accountToAdd = Account::factory()->create();
        $accountToAdd->addState(State::findByCode('DIVISION'));

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');
        $this->adminUser->givePermissionTo('waiting-lists.add-accounts.*');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewWaitingList::class, ['record' => $waitingList->id])
            ->callPageAction('add_student')
            ->assertDontSee('Join date');
    }

    public function test_admin_can_add_student_with_join_date_if_specified()
    {
        $waitingList = factory(WaitingList::class)->create(['department' => 'atc']);
        $accountToAdd = Account::factory()->create();
        $accountToAdd->addState(State::findByCode('DIVISION'));

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');
        $this->adminUser->givePermissionTo('waiting-lists.add-accounts.*');
        $this->adminUser->givePermissionTo('waiting-lists.add-accounts-admin.*');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewWaitingList::class, ['record' => $waitingList->id])
            ->callPageAction('add_student', data: [
                'account_id' => $accountToAdd->id,
                'join_date' => '2020-01-01',
            ]);

        $this->assertContains($accountToAdd->id, $waitingList->fresh()->accounts->pluck('id'));

        $this->assertDatabaseHas('training_waiting_list_account', [
            'list_id' => $waitingList->id,
            'account_id' => $accountToAdd->id,
            'created_at' => '2020-01-01 00:00:00',
        ]);
    }

    public function test_admin_can_add_manual_flag_to_waiting_list()
    {
        $waitingList = factory(WaitingList::class)->create(['department' => 'atc']);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');
        $this->adminUser->givePermissionTo('waiting-lists.add-flags.*');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewWaitingList::class, ['record' => $waitingList->id])
            ->callPageAction('add_flag', data: [
                'name' => 'test',
            ]);

        $this->assertDatabaseHas('training_waiting_list_flags', [
            'list_id' => $waitingList->id,
            'name' => 'test',
            'endorsement_id' => null,
        ]);
    }

    public function test_admin_cant_add_duplicate_named_flag_in_list()
    {
        $waitingList = factory(WaitingList::class)->create(['department' => 'atc']);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');
        $this->adminUser->givePermissionTo('waiting-lists.add-flags.*');

        $flag = factory(WaitingListFlag::class)->create([
            'list_id' => $waitingList->id,
            'name' => 'test',
        ]);
        $waitingList->addFlag($flag);

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewWaitingList::class, ['record' => $waitingList->fresh()->id])
            ->callPageAction('add_flag', data: [
                'name' => 'test',
            ])
            ->assertHasPageActionErrors(['name' => 'unique']);
    }

    public function test_admin_can_create_flag_with_linked_endorsement()
    {
        $waitingList = factory(WaitingList::class)->create(['department' => 'atc']);
        $endorsement = factory(Endorsement::class)->create();

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');
        $this->adminUser->givePermissionTo('waiting-lists.add-flags.*');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewWaitingList::class, ['record' => $waitingList->id])
            ->callPageAction('add_flag', data: [
                'name' => 'test',
                'endorsement_id' => $endorsement->id,
            ]);

        $this->assertDatabaseHas('training_waiting_list_flags', [
            'list_id' => $waitingList->id,
            'name' => 'test',
            'endorsement_id' => $endorsement->id,
        ]);
    }

    public function test_flag_cannot_be_added_without_permission()
    {
        $waitingList = factory(WaitingList::class)->create(['department' => 'atc']);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewWaitingList::class, ['record' => $waitingList->id])
            ->assertPageActionHidden('add_flag');
    }

    public function test_can_view_account_in_waiting_list()
    {
        Livewire::actingAs($this->adminUser);

        $waitingList = factory(WaitingList::class)->create(['department' => 'atc']);
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $waitingList->addToWaitingList($account, $this->adminUser);
        $waitingList->refresh();

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');

        Livewire::test(AccountsRelationManager::class, ['ownerRecord' => $waitingList])
            ->callTableAction('view', record: $waitingList->accounts->first());
        // ->assertSee('Base Information');
    }
}
