<?php

namespace Tests\Feature\Admin\Account;

use App\Filament\Resources\AccountResource;
use App\Filament\Resources\AccountResource\Pages\ViewAccount;
use App\Jobs\UpdateMember;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class ViewAccountPageTest extends BaseAdminTestCase
{
    public function test_page_renders()
    {
        $this->actingAsSuperUser();
        $this->get(AccountResource::getUrl('view', ['record' => $this->user->id]))->assertSuccessful();
    }

    public function test_cant_impersonate_without_permission()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');
        Livewire::actingAs($this->user);
        Livewire::test(ViewAccount::class, ['record' => $this->privacc->id])->assertPageActionHidden('impersonate');
    }

    public function test_can_impersonate_with_permission()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');
        $this->user->givePermissionTo('account.impersonate.*');
        Livewire::actingAs($this->user);
        Livewire::test(ViewAccount::class, ['record' => $this->privacc->id])
            ->assertPageActionVisible('impersonate')
            ->callPageAction('impersonate', data: ['reason' => 'Some reason for impersonating a user']);

        $this->assertEquals($this->privacc->id, auth()->user()->id);
    }

    public function test_can_request_update()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');
        Livewire::actingAs($this->user);

        Bus::fake();

        Livewire::test(ViewAccount::class, ['record' => $this->privacc->id])
            ->assertPageActionVisible('request_central_update')
            ->callPageAction('request_central_update');

        Bus::assertDispatched(UpdateMember::class, fn (UpdateMember $job) => $job->accountID === $this->privacc->id);
    }
}
