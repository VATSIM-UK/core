<?php

namespace Tests\Feature\Admin\Account\Pages;

use App\Filament\Resources\AccountResource\Pages\ViewAccount;
use App\Jobs\UpdateMember;
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
}
