<?php

namespace Tests\Unit\Policies;

use App\Models\Mship\Account;
use App\Models\Training\Seminar\Seminar;
use App\Policies\Training\SeminarPolicy;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SeminarPolicyTest extends TestCase
{
    use DatabaseTransactions;

    private SeminarPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = app(SeminarPolicy::class);
    }

    #[Test]
    public function view_any_allows_when_user_has_atc_or_pilot_view_permission(): void
    {
        $atcOnly = $this->accountWith('training.seminars.view.atc');
        $pilotOnly = $this->accountWith('training.seminars.view.pilot');
        $wildcard = $this->accountWith('training.seminars.view.*');
        $neither = Account::factory()->createQuietly();

        $this->assertTrue($this->policy->viewAny($atcOnly));
        $this->assertTrue($this->policy->viewAny($pilotOnly));
        $this->assertTrue($this->policy->viewAny($wildcard));
        $this->assertFalse($this->policy->viewAny($neither));
    }

    #[Test]
    public function view_respects_department_permissions(): void
    {
        $atcOnly = $this->accountWith('training.seminars.view.atc');
        $pilotOnly = $this->accountWith('training.seminars.view.pilot');

        $this->assertTrue($this->policy->view($atcOnly, $this->seminar('OBS to S1 Training')));
        $this->assertFalse($this->policy->view($atcOnly, $this->seminar('P1 Training')));
        $this->assertFalse($this->policy->view($atcOnly, $this->seminar('P4 Training')));

        $this->assertTrue($this->policy->view($pilotOnly, $this->seminar('P1 Training')));
        $this->assertTrue($this->policy->view($pilotOnly, $this->seminar('P2 Training')));
        $this->assertTrue($this->policy->view($pilotOnly, $this->seminar('P3 Training')));
        $this->assertTrue($this->policy->view($pilotOnly, $this->seminar('P4 Training')));
        $this->assertFalse($this->policy->view($pilotOnly, $this->seminar('OBS to S1 Training')));
    }

    #[Test]
    public function view_allows_wildcard_for_every_training_group(): void
    {
        $wildcard = $this->accountWith('training.seminars.view.*');

        foreach (Seminar::ALLOWED_TRAINING_GROUPS as $trainingGroup) {
            $this->assertTrue($this->policy->view($wildcard, $this->seminar($trainingGroup)));
        }
    }

    #[Test]
    public function view_denies_without_a_permission(): void
    {
        $account = Account::factory()->createQuietly();

        $this->assertFalse($this->policy->viewAny($account));
        $this->assertFalse($this->policy->view($account, $this->seminar('OBS to S1 Training')));
        $this->assertSame([], $this->policy->allowedTrainingGroups($account, 'view'));
    }

    #[Test]
    public function view_does_not_follow_manage_permissions(): void
    {
        $manager = $this->accountWith('training.seminars.manage.atc');

        $this->assertFalse($this->policy->view($manager, $this->seminar('OBS to S1 Training')));
        $this->assertTrue($this->policy->update($manager, $this->seminar('OBS to S1 Training')));
    }

    #[Test]
    public function update_and_delete_respect_department_permissions(): void
    {
        $atcManager = $this->accountWith('training.seminars.manage.atc');
        $pilotManager = $this->accountWith('training.seminars.manage.pilot');

        $this->assertTrue($this->policy->update($atcManager, $this->seminar('OBS to S1 Training')));
        $this->assertTrue($this->policy->delete($atcManager, $this->seminar('OBS to S1 Training')));
        $this->assertFalse($this->policy->update($atcManager, $this->seminar('P1 Training')));
        $this->assertFalse($this->policy->delete($atcManager, $this->seminar('P1 Training')));

        $this->assertTrue($this->policy->update($pilotManager, $this->seminar('P3 Training')));
        $this->assertTrue($this->policy->delete($pilotManager, $this->seminar('P3 Training')));
        $this->assertFalse($this->policy->update($pilotManager, $this->seminar('OBS to S1 Training')));
        $this->assertFalse($this->policy->delete($pilotManager, $this->seminar('OBS to S1 Training')));
    }

    #[Test]
    public function update_and_delete_allow_wildcard_manage_permission(): void
    {
        $wildcard = $this->accountWith('training.seminars.manage.*');

        foreach (Seminar::ALLOWED_TRAINING_GROUPS as $trainingGroup) {
            $this->assertTrue($this->policy->update($wildcard, $this->seminar($trainingGroup)));
            $this->assertTrue($this->policy->delete($wildcard, $this->seminar($trainingGroup)));
        }
    }

    #[Test]
    public function create_allows_any_manage_permission(): void
    {
        $this->assertTrue($this->policy->create($this->accountWith('training.seminars.manage.atc')));
        $this->assertTrue($this->policy->create($this->accountWith('training.seminars.manage.pilot')));
        $this->assertTrue($this->policy->create($this->accountWith('training.seminars.manage.*')));

        $this->assertFalse($this->policy->create($this->accountWith('training.seminars.view.atc')));
        $this->assertFalse($this->policy->create(Account::factory()->createQuietly()));
    }

    #[Test]
    public function allowed_training_groups_returns_only_permitted_groups(): void
    {
        $this->assertSame(
            ['OBS to S1 Training'],
            $this->policy->allowedTrainingGroups($this->accountWith('training.seminars.view.atc'), 'view')
        );

        $this->assertSame(
            ['P1 Training', 'P2 Training', 'P3 Training', 'P4 Training'],
            $this->policy->allowedTrainingGroups($this->accountWith('training.seminars.view.pilot'), 'view')
        );

        $this->assertSame(
            ['OBS to S1 Training'],
            $this->policy->allowedTrainingGroups($this->accountWith('training.seminars.manage.atc'), 'manage')
        );

        $this->assertSame(
            Seminar::ALLOWED_TRAINING_GROUPS,
            $this->policy->allowedTrainingGroups($this->accountWith('training.seminars.manage.*'), 'manage')
        );
    }

    #[Test]
    public function view_and_update_fail_closed_for_unset_or_unknown_training_groups(): void
    {
        $wildcard = $this->accountWith('training.seminars.view.*', 'training.seminars.manage.*');

        foreach ([null, 'Unknown Training'] as $trainingGroup) {
            $seminar = new Seminar(['training_group' => $trainingGroup]);

            $this->assertFalse($this->policy->view($wildcard, $seminar));
            $this->assertFalse($this->policy->update($wildcard, $seminar));
            $this->assertFalse($this->policy->delete($wildcard, $seminar));
        }
    }

    #[Test]
    public function gate_enforces_the_policy(): void
    {
        $atcViewer = $this->accountWith('training.seminars.view.atc');
        $atcManager = $this->accountWith('training.seminars.manage.atc');

        $obs = $this->seminar('OBS to S1 Training');
        $pilot = $this->seminar('P1 Training');

        $this->assertTrue($atcViewer->can('viewAny', Seminar::class));
        $this->assertTrue($atcViewer->can('view', $obs));
        $this->assertFalse($atcViewer->can('view', $pilot));
        $this->assertFalse($atcViewer->can('update', $obs));

        $this->assertTrue($atcManager->can('create', Seminar::class));
        $this->assertTrue($atcManager->can('update', $obs));
        $this->assertFalse($atcManager->can('update', $pilot));
    }

    /**
     * Quiet creation avoids the default role, which carries environment-specific
     * permissions, so each case depends only on the permissions given here.
     */
    private function accountWith(string ...$permissions): Account
    {
        $account = Account::factory()->createQuietly();
        $account->givePermissionTo($permissions);

        return $account;
    }

    private function seminar(string $trainingGroup): Seminar
    {
        return new Seminar(['training_group' => $trainingGroup]);
    }
}
