<?php

namespace Tests\Feature\Admin\WaitingLists;

use App\Filament\Admin\Resources\WaitingListResource\Pages\ViewWaitingList;
use App\Filament\Admin\Resources\WaitingListResource\RelationManagers\AccountsRelationManager;
use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Models\Mship\State;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListFlag;
use Filament\Tables\Actions\EditAction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class ViewWaitingListPageTest extends BaseAdminTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Livewire::actingAs($this->adminUser);
    }

    public function test_one_relation_manager_tables_are_present()
    {
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');

        Livewire::test(ViewWaitingList::class, ['record' => $waitingList->id])
            ->assertStatus(200);
    }

    public function test_admin_user_cant_add_student_without_permission()
    {
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewWaitingList::class, ['record' => $waitingList->id])
            ->assertDontSee('Add student');
    }

    public function test_home_student_can_be_added()
    {
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
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

        $this->assertDatabaseHas('training_waiting_list_account', [
            'list_id' => $waitingList->id,
            'account_id' => $accountToAdd->id,
        ]);
    }

    public function test_student_cant_be_added_twice()
    {
        Livewire::actingAs($this->adminUser);

        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
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
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
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
            ->assertHasActionErrors(['account_id']);

        $this->assertNotContains($accountToAdd->id, $waitingList->fresh()->waitingListAccounts->pluck('account_id'));

        $this->assertDatabaseMissing('training_waiting_list_account', [
            'list_id' => $waitingList->id,
            'account_id' => $accountToAdd->id,
        ]);
    }

    public function test_cannot_see_join_date_field_without_permission()
    {
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
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
        /** @var WaitingList $waitingList */
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
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

        $this->assertTrue($waitingList->includesAccount($accountToAdd->id));

        $this->assertDatabaseHas('training_waiting_list_account', [
            'list_id' => $waitingList->id,
            'account_id' => $accountToAdd->id,
            'created_at' => '2020-01-01 00:00:00',
        ]);
    }

    public function test_admin_can_add_manual_flag_to_waiting_list()
    {
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);

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
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');
        $this->adminUser->givePermissionTo('waiting-lists.add-flags.*');

        $flag = WaitingListFlag::factory()->create([
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
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
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
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewWaitingList::class, ['record' => $waitingList->id])
            ->assertActionHidden('add_flag');
    }

    public function test_can_view_account_in_waiting_list()
    {
        Livewire::actingAs($this->adminUser);

        /** @var WaitingList $waitingList */
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $waitingList->addToWaitingList($account, $this->adminUser);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');

        Livewire::test(AccountsRelationManager::class, ['ownerRecord' => $waitingList, 'pageClass' => ViewWaitingList::class])
            ->assertCanSeeTableRecords([$waitingList->waitingListAccounts()->first()])
            ->assertTableActionVisible('view', record: $waitingList->waitingListAccounts()->first());
    }

    public function test_cannot_edit_account_in_waiting_list_without_permission()
    {
        Livewire::actingAs($this->adminUser);

        /** @var WaitingList $waitingList */
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $waitingList->addToWaitingList($account, $this->adminUser);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');

        Livewire::test(AccountsRelationManager::class, ['ownerRecord' => $waitingList, 'pageClass' => ViewWaitingList::class])
            ->assertCanSeeTableRecords([$waitingList->waitingListAccounts->first()])
            ->assertTableActionHidden('edit', record: $waitingList->waitingListAccounts->first());
    }

    public function test_can_open_edit_action_with_permission()
    {
        Livewire::actingAs($this->adminUser);

        /** @var WaitingList $waitingList */
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $waitingList->addToWaitingList($account, $this->adminUser);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');
        $this->adminUser->givePermissionTo('waiting-lists.update-accounts.*');

        Livewire::test(AccountsRelationManager::class, ['ownerRecord' => $waitingList, 'pageClass' => ViewWaitingList::class])
            ->assertCanSeeTableRecords([$waitingList->waitingListAccounts->first()])
            ->assertTableActionVisible('edit', record: $waitingList->waitingListAccounts->first());
    }

    public function test_notes_can_be_added_to_waiting_list_account()
    {
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $waitingList->addToWaitingList($account, $this->adminUser);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');
        $this->adminUser->givePermissionTo('waiting-lists.update-accounts.*');

        Livewire::test(AccountsRelationManager::class, ['ownerRecord' => $waitingList->refresh(), 'pageClass' => ViewWaitingList::class])
            ->assertCanSeeTableRecords([$waitingList->waitingListAccounts->first()])
            ->mountTableAction(EditAction::class, record: $waitingList->waitingListAccounts->first())
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
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $manualFlag = WaitingListFlag::factory()->create([
            'list_id' => $waitingList->id,
            'name' => 'Test Manual Flag',
        ]);
        $waitingList->addToWaitingList($account, $this->adminUser);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');
        $this->adminUser->givePermissionTo('waiting-lists.update-accounts.*');

        $waitingListAccount = $waitingList->waitingListAccounts->first();

        Livewire::test(AccountsRelationManager::class, ['ownerRecord' => $waitingList->refresh(), 'pageClass' => ViewWaitingList::class])
            ->assertCanSeeTableRecords([$waitingListAccount])
            ->mountTableAction(EditAction::class, record: $waitingListAccount)
            ->assertSee('Test Manual Flag')
            ->setTableActionData(["flags.{$manualFlag->id}" => true])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('training_waiting_list_account_flag', [
            'waiting_list_account_id' => $waitingListAccount->id,
            'flag_id' => $manualFlag->id,
            'marked_at' => now(),
        ]);
    }

    public function test_can_modify_manual_flag_to_false()
    {
        /** @var WaitingList $waitingList */
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $manualFlag = WaitingListFlag::factory()->create([
            'list_id' => $waitingList->id,
            'name' => 'Test Manual Flag',
        ]);
        $waitingListAccount = $waitingList->addToWaitingList($account, $this->adminUser);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.access');
        $this->adminUser->givePermissionTo('waiting-lists.update-accounts.*');

        // set flag to true
        $waitingListAccount->flags()->sync($manualFlag->id, [
            'marked_at' => now(),
        ]);

        $waitingListAccount = $waitingList->waitingListAccounts->first();

        Livewire::test(AccountsRelationManager::class, ['ownerRecord' => $waitingList->refresh(), 'pageClass' => ViewWaitingList::class])
            ->assertCanSeeTableRecords([$waitingListAccount])
            ->mountTableAction('edit', record: $waitingListAccount)
            ->assertSee('Test Manual Flag')
            ->assertTableActionDataSet([
                "flags.{$manualFlag->id}" => true,
            ])
            ->setTableActionData(["flags.{$manualFlag->id}" => false])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('training_waiting_list_account_flag', [
            'waiting_list_account_id' => $waitingListAccount->id,
            'flag_id' => $manualFlag->id,
            'marked_at' => null,
        ]);
    }

    public function test_account_can_be_removed()
    {
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $waitingList->addToWaitingList($account, $this->adminUser);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.remove-accounts.*');
        $this->adminUser->givePermissionTo('waiting-lists.access');

        $removal_type = WaitingList\RemovalReason::Request->value;

        Livewire::test(AccountsRelationManager::class, ['ownerRecord' => $waitingList->refresh(), 'pageClass' => ViewWaitingList::class])
            ->assertCanSeeTableRecords([$waitingList->waitingListAccounts->first()])
            ->mountTableAction('detachWithReason', record: $waitingList->waitingListAccounts->first())
            ->assertSee('Remove from Waiting List')
            ->setTableActionData(['reason_type' => $removal_type])
            ->callMountedTableAction()
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('training_waiting_list_account', [
            'list_id' => $waitingList->id,
            'account_id' => $account->id,
            'deleted_at' => now(),
            'removed_by' => $this->adminUser->id,
            'removal_type' => $removal_type,
        ]);
    }

    public function test_account_can_be_removed_with_other_reason()
    {
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $waitingList->addToWaitingList($account, $this->adminUser);

        $this->adminUser->givePermissionTo('waiting-lists.view.atc');
        $this->adminUser->givePermissionTo('waiting-lists.remove-accounts.*');
        $this->adminUser->givePermissionTo('waiting-lists.access');

        $removal_type = WaitingList\RemovalReason::Other->value;
        $other_reason = 'for testing';

        Livewire::test(AccountsRelationManager::class, ['ownerRecord' => $waitingList->refresh(), 'pageClass' => ViewWaitingList::class])
            ->assertCanSeeTableRecords([$waitingList->waitingListAccounts->first()])
            ->mountTableAction('detachWithReason', record: $waitingList->waitingListAccounts->first())
            ->assertSee('Remove from Waiting List')
            ->setTableActionData(['reason_type' => $removal_type, 'custom_reason' => $other_reason])
            ->callMountedTableAction()
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('training_waiting_list_account', [
            'list_id' => $waitingList->id,
            'account_id' => $account->id,
            'deleted_at' => now(),
            'removed_by' => $this->adminUser->id,
            'removal_type' => $removal_type,
            'removal_comment' => $other_reason,
        ]);
    }

    public function test_cannot_see_edit_button_when_not_admin()
    {
        $userWithoutPermission = Account::factory()->create();

        $waitingList = WaitingList::factory()->create(['department' => 'atc']);

        $userWithoutPermission->givePermissionTo('waiting-lists.view.atc');
        $userWithoutPermission->givePermissionTo('waiting-lists.access');

        Livewire::actingAs($userWithoutPermission)->test(ViewWaitingList::class, ['record' => $waitingList->id])
            ->assertDontSee('Edit settings');
    }

    public function test_can_see_edit_button_when_admin()
    {
        $userWithPermission = Account::factory()->create();
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);

        $userWithPermission->givePermissionTo('waiting-lists.view.atc');
        $userWithPermission->givePermissionTo('waiting-lists.access');
        $userWithPermission->givePermissionTo('waiting-lists.admin.atc');

        Livewire::actingAs($userWithPermission)->test(ViewWaitingList::class, ['record' => $waitingList->id])
            ->assertSee('Edit settings');
    }
}
