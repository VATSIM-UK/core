<?php

namespace Tests\Feature\Admin\Account\Pages;

use App\Filament\Admin\Resources\BanResource\Pages\ViewBan;
use App\Models\Mship\Account\Ban;
use App\Notifications\Mship\BanModified;
use App\Notifications\Mship\BanRepealed;
use App\Policies\Mship\Account\BanPolicy;
use Livewire\Livewire;
use Notification;
use Tests\Feature\Admin\BaseAdminTestCase;

class ViewBanPageTest extends BaseAdminTestCase
{
    public function test_can_only_modify_when_permitted_by_policy()
    {
        $this->assertActionDependentOnPolicy(ViewBan::class, 'edit', BanPolicy::class, 'update', Ban::factory()->create()->id);
    }

    public function test_can_only_repeal_when_permitted_by_policy()
    {
        $this->assertActionDependentOnPolicy(ViewBan::class, 'repeal', BanPolicy::class, null, Ban::factory()->create()->id);
    }

    public function test_modify_ban_action_works()
    {
        Notification::fake();

        $ban = Ban::factory()->create();
        $this->actingAsSuperUser();

        $this->mockPolicyAction(BanPolicy::class, 'update');
        Livewire::test(ViewBan::class, ['record' => $ban->id])->callAction('edit', ['period_finish' => $this->knownDate, 'extra_info' => 'Ban was updated', 'note' => 'An updated note']);

        $ban = $ban->fresh();

        $this->assertEquals($this->knownDate, $ban->period_finish);
        $this->assertStringContainsString('Ban was updated', $ban->reason_extra);

        Notification::assertSentTo([$ban->account], BanModified::class);
    }

    public function test_repeal_ban_action_works()
    {
        Notification::fake();
        $ban = Ban::factory()->create();
        $this->actingAsAdminUser('account.ban.edit.*');

        $this->mockPolicyAction(BanPolicy::class, 'repeal');
        Livewire::test(ViewBan::class, ['record' => $ban->id])->callAction('repeal', ['reason' => 'repeal reason']);

        $this->assertNotNull($ban->fresh()->repealed_at);
        Notification::assertSentTo([$ban->account], BanRepealed::class);
    }
}
