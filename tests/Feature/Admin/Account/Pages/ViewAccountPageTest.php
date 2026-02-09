<?php

namespace Tests\Feature\Admin\Account\Pages;

use App\Filament\Admin\Resources\AccountResource\Pages\ViewAccount;
use App\Jobs\UpdateMember;
use App\Models\Mship\Note\Type;
use App\Models\Mship\State;
use App\Models\Roster;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class ViewAccountPageTest extends BaseAdminTestCase
{
    public function test_cant_impersonate_without_permission()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');
        Livewire::actingAs($this->user);
        Livewire::test(ViewAccount::class, ['record' => $this->privacc->id])->assertActionHidden('impersonate');
    }

    public function test_can_impersonate_with_permission()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');
        $this->user->givePermissionTo('account.impersonate.*');
        Livewire::actingAs($this->user);
        Livewire::test(ViewAccount::class, ['record' => $this->privacc->id])
            ->assertActionVisible('impersonate')
            ->callAction('impersonate', data: ['reason' => 'Some reason for impersonating a user']);

        $this->assertEquals($this->privacc->id, auth()->user()->id);
    }

    public function test_cant_remove_password_without_permission()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');
        $this->privacc->setPassword('123');
        Livewire::actingAs($this->user);
        Livewire::test(ViewAccount::class, ['record' => $this->privacc->id])->assertActionHidden('remove_password');
    }

    public function test_can_remove_password_with_permission()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');
        $this->user->givePermissionTo('account.remove-password.*');
        $this->privacc->setPassword('123');
        $this->assertTrue($this->privacc->fresh()->hasPassword());

        Livewire::actingAs($this->user);
        Livewire::test(ViewAccount::class, ['record' => $this->privacc->id])
            ->assertActionVisible('remove_password')
            ->callAction('remove_password');

        $this->assertFalse($this->privacc->fresh()->hasPassword());
    }

    public function test_can_remove_password_not_visible_when_no_password()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');
        $this->user->givePermissionTo('account.remove-password.*');
        Livewire::actingAs($this->user);
        Livewire::test(ViewAccount::class, ['record' => $this->privacc->id])
            ->assertActionHidden('remove_password');
    }

    public function test_cant_see_email_address_without_permission()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');

        Livewire::actingAs($this->user);
        Livewire::test(ViewAccount::class, ['record' => $this->privacc->id])
            ->assertFormFieldIsHidden('email');

        $this->user->givePermissionTo('account.view-sensitive.*');
        Livewire::test(ViewAccount::class, ['record' => $this->privacc->id])
            ->assertFormFieldExists('email');
    }

    public function test_cant_see_ban_relation_manager_without_permission()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');

        Livewire::actingAs($this->user);
        Livewire::test(ViewAccount::class, ['record' => $this->privacc->id])
            ->assertDontSee('Bans');

        $this->user->givePermissionTo('account.view-sensitive.*');
        Livewire::test(ViewAccount::class, ['record' => $this->privacc->id])
            ->assertSee('Bans');
    }

    public function test_can_request_update()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');
        Livewire::actingAs($this->user);

        Bus::fake();

        Livewire::test(ViewAccount::class, ['record' => $this->privacc->id])
            ->assertActionVisible('request_central_update')
            ->callAction('request_central_update');

        Bus::assertDispatched(UpdateMember::class, fn (UpdateMember $job) => $job->accountID === $this->privacc->id);
    }

    public function test_records_page_visit_in_admin_log()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');
        Livewire::actingAs($this->user);

        Livewire::test(ViewAccount::class, ['record' => $this->privacc->id]);

        $this->assertDatabaseHas('admin_access_logs', [
            'accessor_account_id' => $this->user->id,
            'loggable_id' => $this->privacc->id,
            'loggable_type' => get_class($this->privacc),
        ]);
    }

    public function test_can_apply_roster_restriction_when_permitted()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');
        $this->user->givePermissionTo('roster.restriction.create');

        // Ensure on roster and has a division state
        $this->privacc->addState(State::findByCode('DIVISION'));
        Roster::create(['account_id' => $this->privacc->getKey()]);

        Livewire::actingAs($this->user)
            ->test(ViewAccount::class, ['record' => $this->privacc->refresh()->getKey()])
            ->callAction('Add roster restriction', ['restriction_note' => 'Test restriction']);

        $note = $this->privacc->roster->restrictionNote;

        $this->assertDatabaseHas('roster', [
            'account_id' => $this->privacc->id,
            'restriction_note_id' => $note->id,
        ]);

        $this->assertDatabaseHas('mship_account_note', [
            'note_type_id' => Type::isShortCode('roster')->first()->id,
            'account_id' => $this->privacc->id,
            'writer_id' => $this->user->id,
            'content' => 'Test restriction',
        ]);
    }

    public function test_cant_apply_roster_restriction_when_not_permitted()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');

        // Ensure on roster and has a division state
        $this->privacc->addState(State::findByCode('DIVISION'));
        Roster::create(['account_id' => $this->privacc->getKey()]);

        Livewire::actingAs($this->user)
            ->test(ViewAccount::class, ['record' => $this->privacc->refresh()->getKey()])
            ->assertActionHidden('Add roster restriction');
    }

    public function test_can_remove_roster_restriction_when_permitted()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');
        $this->user->givePermissionTo('roster.restriction.remove');

        // Ensure on roster and has a division state
        $this->privacc->addState(State::findByCode('DIVISION'));
        $existingNote = $this->privacc->addNote(Type::isShortCode('roster')->first(), 'Test restriction', $this->user);
        $roster = Roster::create(['account_id' => $this->privacc->getKey(), 'restriction_note_id' => $existingNote->id]);

        Livewire::actingAs($this->user)
            ->test(ViewAccount::class, ['record' => $this->privacc->refresh()->getKey()])
            ->callAction('roster_restriction_remove', ['restriction_removal_note' => 'Test removal note']);

        $this->assertDatabaseHas('roster', [
            'account_id' => $this->privacc->id,
            'restriction_note_id' => null,
        ]);

        $this->assertDatabaseHas('mship_account_note', [
            'note_type_id' => Type::isShortCode('roster')->first()->id,
            'account_id' => $this->privacc->id,
            'writer_id' => $this->user->id,
            'content' => 'Test removal note',
        ]);
    }

    public function test_cant_remove_roster_restriction_when_not_permitted()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');

        // Ensure on roster and has a division state
        $this->privacc->addState(State::findByCode('DIVISION'));
        $existingNote = $this->privacc->addNote(Type::isShortCode('roster')->first(), 'Test restriction', $this->user);
        Roster::create(['account_id' => $this->privacc->getKey(), 'restriction_note_id' => $existingNote->id]);

        Livewire::actingAs($this->user)
            ->test(ViewAccount::class, ['record' => $this->privacc->refresh()->getKey()])
            ->assertActionHidden('roster_restriction_remove');
    }

    public function test_it_returns_the_most_recent_uk_atc_session_disconnect_time()
    {
        $olderSession = factory(\App\Models\NetworkData\Atc::class)
            ->states('offline')
            ->create(['account_id' => $this->privacc->id, 'disconnected_at' => now()->subDays(2)]);

        $newerSession = factory(\App\Models\NetworkData\Atc::class)
            ->states('offline')
            ->create(['account_id' => $this->privacc->id, 'disconnected_at' => now()->subHour()]);

        $lastSeen = $this->privacc->lastSeenControllingUK();

        $this->assertNotNull($lastSeen);
        $this->assertTrue($lastSeen->equalTo($newerSession->disconnected_at));
    }

    public function test_it_returns_null_when_no_uk_atc_sessions_exist()
    {
        $this->assertNull($this->privacc->lastSeenControllingUK());
    }

    public function test_cant_revoke_visiting_status_without_permission()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');
        $this->privacc->addState(State::findByCode('VISITING'));

        Livewire::actingAs($this->user);
        Livewire::test(ViewAccount::class, ['record' => $this->privacc->id])
            ->assertActionHidden('revoke_visiting_status');
    }

    public function test_can_revoke_visiting_status_when_permitted()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');
        $this->user->givePermissionTo('vt.status.revoke');

        $this->privacc->addState(State::findByCode('VISITING'));

        Livewire::actingAs($this->user);
        Livewire::test(ViewAccount::class, ['record' => $this->privacc->refresh()->id])
            ->assertActionVisible('revoke_visiting_status')
            ->callAction('revoke_visiting_status');

        $this->assertFalse($this->privacc->fresh()->hasState('VISITING'));
    }

    public function test_revoke_visiting_status_creates_a_note()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');
        $this->user->givePermissionTo('vt.status.revoke');

        $this->privacc->addState(State::findByCode('VISITING'));

        Livewire::actingAs($this->user);

        Livewire::test(ViewAccount::class, ['record' => $this->privacc->refresh()->id])
            ->callAction('revoke_visiting_status');

        $this->assertDatabaseHas('mship_account_note', [
            'account_id' => $this->privacc->id,
            'writer_id' => $this->user->id,
            'content' => 'Visiting status revoked by ' . $this->user->name,
        ]);
    }

    public function test_revoke_visiting_status_not_visible_without_visiting_state()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');
        $this->user->givePermissionTo('vt.status.revoke.*');

        Livewire::actingAs($this->user);

        Livewire::test(ViewAccount::class, ['record' => $this->privacc->id])
            ->assertActionHidden('revoke_visiting_status');
    }
}
