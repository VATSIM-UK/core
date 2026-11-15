<?php

namespace Tests\Feature\Admin\Account\Pages;

use App\Filament\Admin\Resources\Accounts\Pages\EditAccount;
use App\Models\Mship\Account;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class EditAccountPageTest extends BaseAdminTestCase
{
    public function test_gated_attributes_are_absent_from_form_data_without_sensitive_permission(): void
    {
        $target = $this->makeTargetAccount();

        $this->actingAsAdminUser([
            'account.view-insensitive.*',
            'account.edit-basic-details.*',
        ]);

        $data = Livewire::test(EditAccount::class, ['record' => $target->id])->get('data');

        $this->assertArrayNotHasKey('email', $data);
        $this->assertArrayNotHasKey('last_login_ip', $data);
    }

    public function test_gated_attributes_are_present_from_form_data_with_sensitive_permission(): void
    {
        $target = $this->makeTargetAccount();

        $this->actingAsAdminUser([
            'account.view-insensitive.*',
            'account.view-sensitive.*',
            'account.edit-basic-details.*',
        ]);

        $component = Livewire::test(EditAccount::class, ['record' => $target->id]);
        $data = $component->get('data');

        $this->assertSame($target->email, $data['email']);
        $this->assertSame($target->last_login_ip, $data['last_login_ip']);

        $component->set('data.nickname', 'New Nickname')->call('save');

        $this->assertSame('New Nickname', $target->refresh()->nickname);
    }

    private function makeTargetAccount(): Account
    {
        $target = Account::factory()->createQuietly(['email' => 'sensitive@example.com']);
        $target->last_login_ip = '10.11.12.13';
        $target->saveQuietly();

        return $target;
    }
}
