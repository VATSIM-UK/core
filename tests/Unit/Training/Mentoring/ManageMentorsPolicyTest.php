<?php

declare(strict_types=1);

namespace Tests\Unit\Training\Mentoring;

use App\Models\Mship\Account;
use App\Models\Training\Mentoring\ManageMentorsScope;
use App\Policies\Training\Mentoring\ManageMentorsPolicy;
use App\Services\Training\MentorPermissionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ManageMentorsPolicyTest extends TestCase
{
    use DatabaseTransactions;

    private ManageMentorsPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = app(ManageMentorsPolicy::class);
    }

    #[Test]
    public function view_any_allows_atc_or_pilot_view_permissions(): void
    {
        $atcViewer = Account::factory()->create();
        $atcViewer->givePermissionTo('training.mentors.view.atc');

        $pilotViewer = Account::factory()->create();
        $pilotViewer->givePermissionTo('training.mentors.view.pilot');

        $this->assertTrue($this->policy->viewAny($atcViewer));
        $this->assertTrue($this->policy->viewAny($pilotViewer));
    }

    #[Test]
    public function view_any_denies_users_without_manage_mentor_permissions(): void
    {
        $account = Account::factory()->create();

        $this->assertFalse($this->policy->viewAny($account));
    }

    #[Test]
    public function view_category_respects_atc_and_pilot_scopes(): void
    {
        $atcViewer = Account::factory()->create();
        $atcViewer->givePermissionTo('training.mentors.view.atc');

        $atcCategory = MentorPermissionService::atcCategories()[0];
        $pilotCategory = MentorPermissionService::pilotCategories()[0];

        $this->assertTrue($this->policy->viewCategory($atcViewer, new ManageMentorsScope, $atcCategory));
        $this->assertFalse($this->policy->viewCategory($atcViewer, new ManageMentorsScope, $pilotCategory));
    }

    #[Test]
    public function manage_category_requires_manage_permission_for_category_type(): void
    {
        $manager = Account::factory()->create();
        $manager->givePermissionTo('training.mentors.manage.atc');

        $atcCategory = MentorPermissionService::atcCategories()[0];
        $pilotCategory = MentorPermissionService::pilotCategories()[0];

        $this->assertTrue($this->policy->manageCategory($manager, new ManageMentorsScope, $atcCategory));
        $this->assertFalse($this->policy->manageCategory($manager, new ManageMentorsScope, $pilotCategory));
    }
}
