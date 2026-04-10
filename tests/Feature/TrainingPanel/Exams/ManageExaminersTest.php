<?php

namespace Tests\Feature\TrainingPanel\Exams;

use App\Filament\Training\Pages\Exam\ManageExaminers;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
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
    public function it_loads_when_user_has_view_pilot_permission(): void
    {
        $this->panelUser->givePermissionTo('training.examiners.view.pilot');

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
    public function it_defaults_to_obs_role_for_atc_only_users(): void
    {
        $this->panelUser->givePermissionTo('training.examiners.view.atc');

        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class)
            ->assertSet('role', 'obs');
    }

    #[Test]
    public function it_defaults_to_p1_when_user_lacks_atc_view_permission(): void
    {
        $this->panelUser->givePermissionTo('training.examiners.view.pilot');

        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class)
            ->assertSet('role', 'p1');
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
    public function it_prevents_pilot_only_user_accessing_atc_role_via_url(): void
    {
        $this->panelUser->givePermissionTo('training.examiners.view.pilot');

        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class, ['role' => 'obs'])
            ->assertSet('role', 'p1');
    }

    #[Test]
    public function it_prevents_atc_only_user_accessing_pilot_role_via_url(): void
    {
        $this->panelUser->givePermissionTo('training.examiners.view.atc');

        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class, ['role' => 'p1'])
            ->assertSet('role', 'obs');
    }

    #[Test]
    public function it_lists_accounts_with_the_selected_atc_examiner_role(): void
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

    public static function pilotRoleProvider(): array
    {
        return [
            'p1' => ['p1', 'Pilot Examiner (P1)'],
            'p2' => ['p2', 'Pilot Examiner (P2)'],
            'p3' => ['p3', 'Pilot Examiner (P3)'],
        ];
    }

    #[Test]
    #[DataProvider('pilotRoleProvider')]
    public function it_lists_pilot_examiners_for_the_correct_role(string $roleKey, string $roleName): void
    {
        $this->panelUser->givePermissionTo('training.examiners.view.pilot');

        $matchingExaminer = Account::factory()->create();
        $matchingExaminer->assignRole(Role::findByName($roleName, 'web'));

        $otherPilotRoleKey = $roleKey === 'p1' ? 'p2' : 'p1';
        $otherRoleName = $otherPilotRoleKey === 'p1' ? 'Pilot Examiner (P1)' : 'Pilot Examiner (P2)';
        $otherExaminer = Account::factory()->create();
        $otherExaminer->assignRole(Role::findByName($otherRoleName, 'web'));

        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class, ['role' => $roleKey])
            ->assertSet('role', $roleKey)
            ->assertSee($matchingExaminer->name)
            ->assertDontSee($otherExaminer->name);
    }

    #[Test]
    public function it_does_not_show_atc_examiners_on_pilot_role_pages(): void
    {
        $this->panelUser->givePermissionTo([
            'training.examiners.view.atc',
            'training.examiners.view.pilot',
        ]);

        $atcExaminer = Account::factory()->create();
        $atcExaminer->assignRole(Role::findByName('ATC Examiner (OBS)', 'web'));

        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class, ['role' => 'p1'])
            ->assertDontSee($atcExaminer->name);
    }

    #[Test]
    public function it_hides_manage_actions_when_user_lacks_manage_atc_permission(): void
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
    public function it_hides_manage_actions_for_pilot_role_when_user_lacks_manage_pilot_permission(): void
    {
        $this->panelUser->givePermissionTo([
            'training.examiners.view.atc',
            'training.examiners.view.pilot',
        ]);

        $pilotExaminer = Account::factory()->create();
        $pilotExaminer->assignRole(Role::findByName('Pilot Examiner (P1)', 'web'));

        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class, ['role' => 'p1'])
            ->assertTableActionHidden('addMember')
            ->assertTableActionHidden('remove', $pilotExaminer);
    }

    #[Test]
    public function it_shows_manage_actions_for_pilot_role_when_user_has_manage_pilot_permission(): void
    {
        $this->panelUser->givePermissionTo([
            'training.examiners.view.pilot',
            'training.examiners.manage.pilot',
        ]);

        $pilotExaminer = Account::factory()->create();
        $pilotExaminer->assignRole(Role::findByName('Pilot Examiner (P1)', 'web'));

        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class, ['role' => 'p1'])
            ->assertTableActionVisible('addMember')
            ->assertTableActionVisible('remove', $pilotExaminer);
    }

    #[Test]
    public function it_can_assign_the_active_atc_examiner_role_to_an_account(): void
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
    public function it_can_remove_the_active_atc_examiner_role_from_an_account(): void
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

    #[Test]
    #[DataProvider('pilotRoleProvider')]
    public function it_can_assign_a_pilot_examiner_role_to_an_account(string $roleKey, string $roleName): void
    {
        $this->panelUser->givePermissionTo([
            'training.examiners.view.pilot',
            'training.examiners.manage.pilot',
        ]);

        $target = Account::factory()->create();
        $pilotRole = Role::findByName($roleName, 'web');

        $this->assertFalse($target->hasRole($pilotRole));

        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class, ['role' => $roleKey])
            ->callTableAction('addMember', data: ['account_id' => $target->id])
            ->assertHasNoTableActionErrors();

        $this->assertTrue($target->fresh()->hasRole($pilotRole));
    }

    #[Test]
    #[DataProvider('pilotRoleProvider')]
    public function it_can_remove_a_pilot_examiner_role_from_an_account(string $roleKey, string $roleName): void
    {
        $this->panelUser->givePermissionTo([
            'training.examiners.view.pilot',
            'training.examiners.manage.pilot',
        ]);

        $pilotExaminer = Account::factory()->create();
        $pilotRole = Role::findByName($roleName, 'web');
        $pilotExaminer->assignRole($pilotRole);

        $this->assertTrue($pilotExaminer->hasRole($pilotRole));

        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class, ['role' => $roleKey])
            ->callTableAction('remove', $pilotExaminer)
            ->assertHasNoTableActionErrors();

        $this->assertFalse($pilotExaminer->fresh()->hasRole($pilotRole));
    }

    #[Test]
    public function it_does_not_assign_the_wrong_pilot_role_when_active_role_is_p2(): void
    {
        $this->panelUser->givePermissionTo([
            'training.examiners.view.pilot',
            'training.examiners.manage.pilot',
        ]);

        $target = Account::factory()->create();

        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class, ['role' => 'p2'])
            ->callTableAction('addMember', data: ['account_id' => $target->id])
            ->assertHasNoTableActionErrors();

        $this->assertTrue($target->fresh()->hasRole('Pilot Examiner (P2)'));
        $this->assertFalse($target->fresh()->hasRole('Pilot Examiner (P1)'));
        $this->assertFalse($target->fresh()->hasRole('Pilot Examiner (P3)'));
    }

    #[Test]
    public function atc_manage_permission_does_not_grant_manage_access_to_pilot_roles(): void
    {
        $this->panelUser->givePermissionTo([
            'training.examiners.view.pilot',
            'training.examiners.manage.atc',
        ]);

        $pilotExaminer = Account::factory()->create();
        $pilotExaminer->assignRole(Role::findByName('Pilot Examiner (P1)', 'web'));

        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class, ['role' => 'p1'])
            ->assertTableActionHidden('addMember')
            ->assertTableActionHidden('remove', $pilotExaminer);
    }

    #[Test]
    public function pilot_manage_permission_does_not_grant_manage_access_to_atc_roles(): void
    {
        $this->panelUser->givePermissionTo([
            'training.examiners.view.atc',
            'training.examiners.manage.pilot',
        ]);

        $atcExaminer = Account::factory()->create();
        $atcExaminer->assignRole(Role::findByName('ATC Examiner (OBS)', 'web'));

        Livewire::actingAs($this->panelUser)
            ->test(ManageExaminers::class)
            ->assertTableActionHidden('addMember')
            ->assertTableActionHidden('remove', $atcExaminer);
    }
}
