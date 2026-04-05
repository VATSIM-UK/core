<?php

namespace Tests\Feature\TrainingPanel\Exams;

use App\Filament\Training\Pages\Exam\ManageExaminers;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class ManageExaminersTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
    }

    #[Test]
    public function it_loads_when_user_has_view_atc_permission(): void
    {
        $this->panelUser->givePermissionTo('training.examiners.view.atc');

        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_loads_when_user_has_view_wildcard_permission(): void
    {
        $this->panelUser->givePermissionTo('training.examiners.view.*');

        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_forbids_access_without_view_permission(): void
    {
        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class)
            ->assertForbidden();
    }

    #[Test]
    public function it_lists_accounts_with_the_selected_examiner_role(): void
    {
        $this->panelUser->givePermissionTo('training.examiners.view.atc');

        $obsExaminer = Account::factory()->create();
        $obsExaminer->assignRole(Role::findByName('ATC Examiner (OBS)', 'web'));

        $twrExaminer = Account::factory()->create();
        $twrExaminer->assignRole(Role::findByName('ATC Examiner (TWR)', 'web'));

        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class)
            ->assertSet('role', 'obs')
            ->assertSee($obsExaminer->name)
            ->assertDontSee($twrExaminer->name);

        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class, ['role' => 'twr'])
            ->assertSet('role', 'twr')
            ->assertSee($twrExaminer->name)
            ->assertDontSee($obsExaminer->name);
    }

    #[Test]
    public function it_normalises_invalid_role_query_to_obs(): void
    {
        $this->panelUser->givePermissionTo('training.examiners.view.atc');

        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class, ['role' => 'invalid'])
            ->assertSet('role', 'obs');
    }

    #[Test]
    public function it_hides_manage_actions_when_user_lacks_manage_permission(): void
    {
        $this->panelUser->givePermissionTo('training.examiners.view.atc');

        $examiner = Account::factory()->create();
        $examiner->assignRole(Role::findByName('ATC Examiner (OBS)', 'web'));

        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class)
            ->assertTableActionHidden('addMember')
            ->assertTableActionHidden('remove', $examiner);
    }

    #[Test]
    public function it_shows_manage_actions_when_user_has_manage_atc_permission(): void
    {
        $this->panelUser->givePermissionTo([
            'training.examiners.view.atc',
            'training.examiners.manage.atc',
        ]);

        $examiner = Account::factory()->create();
        $examiner->assignRole(Role::findByName('ATC Examiner (OBS)', 'web'));

        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class)
            ->assertTableActionVisible('addMember')
            ->assertTableActionVisible('remove', $examiner);
    }

    #[Test]
    public function it_shows_manage_actions_when_user_has_manage_wildcard_permission(): void
    {
        $this->panelUser->givePermissionTo([
            'training.examiners.view.atc',
            'training.examiners.manage.*',
        ]);

        $examiner = Account::factory()->create();
        $examiner->assignRole(Role::findByName('ATC Examiner (OBS)', 'web'));

        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class)
            ->assertTableActionVisible('addMember')
            ->assertTableActionVisible('remove', $examiner);
    }

    #[Test]
    public function it_can_assign_the_active_examiner_role_to_an_account(): void
    {
        $this->panelUser->givePermissionTo([
            'training.examiners.view.atc',
            'training.examiners.manage.atc',
        ]);

        $target = Account::factory()->create();
        $obsRole = Role::findByName('ATC Examiner (OBS)', 'web');

        $this->assertFalse($target->hasRole($obsRole));

        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class)
            ->callTableAction('addMember', data: ['account_id' => $target->id])
            ->assertHasNoTableActionErrors();

        $this->assertTrue($target->fresh()->hasRole($obsRole));
    }

    #[Test]
    public function it_can_remove_the_active_examiner_role_from_an_account(): void
    {
        $this->panelUser->givePermissionTo([
            'training.examiners.view.atc',
            'training.examiners.manage.atc',
        ]);

        $examiner = Account::factory()->create();
        $obsRole = Role::findByName('ATC Examiner (OBS)', 'web');
        $examiner->assignRole($obsRole);

        $this->assertTrue($examiner->hasRole($obsRole));

        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class)
            ->callTableAction('remove', $examiner)
            ->assertHasNoTableActionErrors();

        $this->assertFalse($examiner->fresh()->hasRole($obsRole));
    }
}
