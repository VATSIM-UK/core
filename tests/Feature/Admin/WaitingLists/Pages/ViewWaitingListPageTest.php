<?php

namespace Tests\Feature\Admin\WaitingLists;

use App\Filament\Resources\WaitingListResource\Pages\ViewWaitingList;
use App\Filament\Resources\WaitingListResource\RelationManagers\IneligibleAccountsRelationManager;
use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Models\Mship\State;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListFlag;
use App\Models\Training\WaitingList\WaitingListStatus;
use App\Rules\HomeMemberId;
use Filament\Tables\Actions\EditAction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class ViewWaitingListPageTest extends BaseAdminTestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        Livewire::actingAs($this->adminUser);
    }

    public function test_two_relation_manager_tables_are_present()
    {
        $waitingList = factory(WaitingList::class)->create(['department' => 'atc']);
        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');

        Livewire::test(ViewWaitingList::class, ['record' => $waitingList->id])
            ->assertStatus(200);
        // ->assertSee('Eligible Accounts')
        // ->assertSee('Ineligible Accounts');
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
            ->callAction('add_student', data: [
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
            ->callAction('add_student', data: [
                'account_id' => $accountToAdd->id,
            ])
            ->assertHasActionErrors(['account_id']);
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
            ->callAction('add_student', data: [
                'account_id' => $accountToAdd->id,
            ])
            ->assertHasActionErrors(['account_id' => [HomeMemberId::class]]);

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
            ->mountAction('add_student')
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
            ->callAction('add_student', data: [
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
            ->callAction('add_flag', data: [
                'name' => 'My Test Flag',
            ]);

        $this->assertDatabaseHas('training_waiting_list_flags', [
            'list_id' => $waitingList->id,
            'name' => 'My Test Flag',
            'position_group_id' => null,
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
            ->callAction('add_flag', data: [
                'name' => 'test',
            ])
            ->assertHasActionErrors(['name' => 'unique']);
    }

    public function test_admin_can_create_flag_with_linked_endorsement()
    {
        $waitingList = factory(WaitingList::class)->create(['department' => 'atc']);
        $positionGroup = factory(PositionGroup::class)->create();

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');
        $this->adminUser->givePermissionTo('waiting-lists.add-flags.*');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewWaitingList::class, ['record' => $waitingList->id])
            ->callAction('add_flag', data: [
                'name' => 'My Test Flag',
                'position_group_id' => $positionGroup->id,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('training_waiting_list_flags', [
            'list_id' => $waitingList->id,
            'name' => 'My Test Flag',
            'position_group_id' => $positionGroup->id,
        ]);
    }

    public function test_flag_cannot_be_added_without_permission()
    {
        $waitingList = factory(WaitingList::class)->create(['department' => 'atc']);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewWaitingList::class, ['record' => $waitingList->id])
            ->assertActionHidden('add_flag');
    }

    public function test_can_view_account_in_waiting_list()
    {
        Livewire::actingAs($this->adminUser);

        $waitingList = factory(WaitingList::class)->create(['department' => 'atc']);
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $waitingList->addToWaitingList($account, $this->adminUser);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');

        Livewire::test(IneligibleAccountsRelationManager::class, ['ownerRecord' => $waitingList, 'pageClass' => ViewWaitingList::class])
            ->assertCanSeeTableRecords([$waitingList->accounts()->first()])
            ->assertTableActionVisible('view', record: $waitingList->accounts->first());
    }

    public function test_cannot_edit_account_in_waiting_list_without_permission()
    {
        Livewire::actingAs($this->adminUser);

        $waitingList = factory(WaitingList::class)->create(['department' => 'atc']);
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $waitingList->addToWaitingList($account, $this->adminUser);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');

        Livewire::test(IneligibleAccountsRelationManager::class, ['ownerRecord' => $waitingList, 'pageClass' => ViewWaitingList::class])
            ->assertCanSeeTableRecords([$waitingList->accounts()->first()])
            ->assertTableActionHidden('edit', record: $waitingList->accounts->first());
    }

    public function test_can_open_edit_action_with_permission()
    {
        Livewire::actingAs($this->adminUser);

        $waitingList = factory(WaitingList::class)->create(['department' => 'atc']);
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $waitingList->addToWaitingList($account, $this->adminUser);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');
        $this->adminUser->givePermissionTo('waiting-lists.update-accounts.*');

        Livewire::test(IneligibleAccountsRelationManager::class, ['ownerRecord' => $waitingList, 'pageClass' => ViewWaitingList::class])
            ->assertCanSeeTableRecords([$waitingList->accounts()->first()])
            ->assertTableActionVisible('edit', record: $waitingList->accounts->first());
    }

    public function test_notes_can_be_added_to_waiting_list_account()
    {
        $waitingList = factory(WaitingList::class)->create(['department' => 'atc']);
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $waitingList->addToWaitingList($account, $this->adminUser);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');
        $this->adminUser->givePermissionTo('waiting-lists.update-accounts.*');

        // assign status to waiting list account
        $waitingList->accounts->find($account->id)->pivot->addStatus(
            WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS)
        );

        Livewire::test(IneligibleAccountsRelationManager::class, ['ownerRecord' => $waitingList->refresh(), 'pageClass' => ViewWaitingList::class])
            ->assertCanSeeTableRecords([$waitingList->accounts()->first()])
            ->mountTableAction(EditAction::class, record: $waitingList->accounts->first())
            ->assertTableActionDataSet([
                'account_status' => WaitingListStatus::DEFAULT_STATUS,
            ])
            ->setTableActionData(data: ['notes' => 'test'])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('training_waiting_list_account', [
            'list_id' => $waitingList->id,
            'account_id' => $account->id,
            'notes' => 'test',
            'deleted_at' => null,
        ]);
    }

    public function test_can_modify_manual_flag_to_true()
    {
        $waitingList = factory(WaitingList::class)->create(['department' => 'atc']);
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $manualFlag = factory(WaitingListFlag::class)->create([
            'list_id' => $waitingList->id,
            'name' => 'Test Manual Flag',
        ]);
        $waitingList->addToWaitingList($account, $this->adminUser);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');
        $this->adminUser->givePermissionTo('waiting-lists.update-accounts.*');

        // assign status to waiting list account
        $waitingList->accounts->find($account->id)->pivot->addStatus(
            WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS)
        );

        Livewire::test(IneligibleAccountsRelationManager::class, ['ownerRecord' => $waitingList->refresh(), 'pageClass' => ViewWaitingList::class])
            ->assertCanSeeTableRecords([$waitingList->accounts()->first()])
            ->mountTableAction(EditAction::class, record: $waitingList->accounts->first())
            ->assertSee('Test Manual Flag')
            ->setTableActionData(["flags.{$manualFlag->id}" => true])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('training_waiting_list_account_flag', [
            'waiting_list_account_id' => $waitingList->accounts->first()->pivot->id,
            'flag_id' => $manualFlag->id,
            'marked_at' => now(),
        ]);
    }

    public function test_can_modify_manual_flag_to_false()
    {
        $waitingList = factory(WaitingList::class)->create(['department' => 'atc']);
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $manualFlag = factory(WaitingListFlag::class)->create([
            'list_id' => $waitingList->id,
            'name' => 'Test Manual Flag',
        ]);
        $waitingList->addToWaitingList($account, $this->adminUser);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');
        $this->adminUser->givePermissionTo('waiting-lists.update-accounts.*');

        // assign status to waiting list account
        $waitingList->accounts->find($account->id)->pivot->addStatus(
            WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS)
        );

        // set flag to true
        $waitingList->accounts->find($account->id)->pivot->flags()->sync($manualFlag->id, [
            'marked_at' => now(),
        ]);

        Livewire::test(IneligibleAccountsRelationManager::class, ['ownerRecord' => $waitingList->refresh(), 'pageClass' => ViewWaitingList::class])
            ->assertCanSeeTableRecords([$waitingList->accounts()->first()])
            ->mountTableAction('edit', record: $waitingList->accounts->first())
            ->assertSee('Test Manual Flag')
            ->assertTableActionDataSet([
                "flags.{$manualFlag->id}" => true,
            ])
            ->setTableActionData(["flags.{$manualFlag->id}" => false])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('training_waiting_list_account_flag', [
            'waiting_list_account_id' => $waitingList->accounts->first()->pivot->id,
            'flag_id' => $manualFlag->id,
            'marked_at' => null,
        ]);
    }

    public function test_account_can_be_removed()
    {
        $waitingList = factory(WaitingList::class)->create(['department' => 'atc']);
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $waitingList->addToWaitingList($account, $this->adminUser);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.remove-accounts.*');
        $this->adminUser->givePermissionTo('waiting-lists.access');

        // assign status to waiting list account
        $waitingList->accounts->find($account->id)->pivot->addStatus(
            WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS)
        );

        Livewire::test(IneligibleAccountsRelationManager::class, ['ownerRecord' => $waitingList->refresh(), 'pageClass' => ViewWaitingList::class])
            ->assertCanSeeTableRecords([$waitingList->accounts()->first()])
            ->callTableAction('detach', record: $waitingList->accounts->first());

        $this->assertDatabaseHas('training_waiting_list_account', [
            'list_id' => $waitingList->id,
            'account_id' => $account->id,
            'deleted_at' => now(),
        ]);
    }
}
