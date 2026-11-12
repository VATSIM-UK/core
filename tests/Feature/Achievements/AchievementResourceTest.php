<?php

namespace Tests\Feature\Achievements;

use App\Filament\Admin\Resources\Achievements\AchievementResource;
use App\Filament\Admin\Resources\Achievements\Pages\AwardedMembers;
use App\Filament\Admin\Resources\Achievements\Pages\CreateAchievement;
use App\Filament\Admin\Resources\Achievements\Pages\EditAchievement;
use App\Filament\Admin\Resources\Achievements\Pages\ListAchievements;
use App\Models\Mship\Account;
use App\Models\Mship\Achievement;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class AchievementResourceTest extends BaseAdminTestCase
{
    use DatabaseTransactions;

    public function test_admin_can_access_achievement_index_page()
    {
        $this->actingAsAdminUser('achievements.view');

        $this->get(AchievementResource::getUrl('index'))
            ->assertSuccessful();

    }

    public function test_it_cannot_access_achievement_index_page_without_permission()
    {
        $this->actingAsAdminUser();

        $this->get(AchievementResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_create_action_is_visible_to_users_with_permission()
    {
        $this->actingAsAdminUser('achievements.manage');

        Livewire::test(ListAchievements::class)
            ->assertActionVisible('create');
    }

    public function test_it_requires_name_description_and_image_to_create_achievement()
    {
        $this->actingAsAdminUser('achievements.manage');

        Livewire::test(CreateAchievement::class)
            ->fillForm([
                'name' => null,
                'description' => null,
                'image' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'description' => 'required', 'image' => 'required']);
    }

    public function test_it_can_create_achievement()
    {

        Storage::fake('public');
        $this->actingAsAdminUser('achievements.manage');

        Livewire::test(CreateAchievement::class)
            ->fillForm([
                'name' => 'Test Achievement',
                'description' => 'This is a test achievement.',
                'image' => UploadedFile::fake()->image('achievement.png', 64, 64),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $achievement = Achievement::query()->where('name', 'Test Achievement')->firstOrFail();

        $this->assertDatabaseHas('mship_achievement', [
            'id' => $achievement->id,
            'name' => 'Test Achievement',
            'description' => 'This is a test achievement.',
            'created_by' => auth()->user()->id,
            'updated_by' => auth()->user()->id,
        ]);

        Storage::disk('public')->assertExists($achievement->image);
    }

    public function test_view_permission_does_not_allow_edit_actions()
    {
        $this->actingAsAdminUser('achievements.view');

        $achievement = Achievement::factory()->create();

        $this->get(AchievementResource::getUrl('edit', ['record' => $achievement->id]))
            ->assertForbidden();
    }

    public function test_manage_permission_allows_edit_actions()
    {
        $this->actingAsAdminUser('achievements.manage');

        $achievement = Achievement::factory()->create();

        $this->get(AchievementResource::getUrl('edit', ['record' => $achievement->id]))
            ->assertSuccessful();
    }

    public function test_edit_action()
    {
        Storage::fake('public');
        $this->actingAsAdminUser('achievements.manage');

        $achievement = Achievement::factory()->create([
            'name' => 'Original Achievement',
            'image' => UploadedFile::fake()->image('original.png', 64, 64)->store('achievements', 'public'),
        ]);

        Livewire::test(EditAchievement::class, ['record' => $achievement->id])
            ->fillForm([
                'name' => 'Updated Achievement',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('mship_achievement', [
            'id' => $achievement->id,
            'name' => 'Updated Achievement',
            'updated_by' => auth()->user()->id,
        ]);
    }

    public function test_it_can_delete_achievement()
    {
        $this->actingAsAdminUser('achievements.manage');

        $achievement = Achievement::factory()->create();

        Livewire::test(EditAchievement::class, ['record' => $achievement->id])
            ->callAction('delete')
            ->assertRedirect(AchievementResource::getUrl('index'));

        $this->assertSoftDeleted('mship_achievement', [
            'id' => $achievement->id,
        ]);

        $this->assertDatabaseHas('mship_achievement', [
            'id' => $achievement->id,
            'deleted_by' => auth()->user()->id,
        ]);
    }

    public function test_award_action_is_hidden_without_permission()
    {
        $this->actingAsAdminUser('achievements.view');

        Livewire::test(ListAchievements::class)
            ->assertActionHidden('award');
    }

    public function test_award_action_is_visible_with_permission()
    {
        $this->actingAsAdminUser(['achievements.view', 'achievements.award']);

        Livewire::test(ListAchievements::class)
            ->assertActionVisible('award');
    }

    public function test_it_can_award_achievement_to_multiple_accounts()
    {
        $this->actingAsAdminUser(['achievements.view', 'achievements.award']);

        $firstAccount = Account::factory()->create();
        $secondAccount = Account::factory()->create();

        $firstAchievement = Achievement::factory()->create();
        $secondAchievement = Achievement::factory()->create();

        Livewire::test(ListAchievements::class)
            ->callAction('award', [
                'account_id' => [
                    $firstAccount->id,
                    $secondAccount->id,
                ],
                'achievements' => [
                    $firstAchievement->id,
                    $secondAchievement->id,
                ],
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseCount('mship_achievement_award', 4);
    }

    public function test_it_cannot_award_achievement_to_self()
    {
        $this->actingAsAdminUser(['achievements.view', 'achievements.award']);

        $achievement = Achievement::factory()->create();

        Livewire::test(ListAchievements::class)
            ->callAction('award', [
                'account_id' => [$this->adminUser->id],
                'achievements' => [$achievement->id],
            ])
            ->assertHasFormErrors(['account_id']);

        $this->assertDatabaseMissing('mship_achievement_award', [
            'account_id' => $this->adminUser->id,
            'achievement_id' => $achievement->id,
        ]);
    }

    public function test_awarding_an_already_awarded_achievement_does_not_create_duplicate()
    {
        $this->actingAsAdminUser(['achievements.view', 'achievements.award']);

        $account = Account::factory()->create();
        $achievement = Achievement::factory()->create();

        // Award the achievement for the first time
        Livewire::test(ListAchievements::class)
            ->callAction('award', [
                'account_id' => [$account->id],
                'achievements' => [$achievement->id],
            ])
            ->assertHasNoFormErrors();

        // Attempt to award the same achievement again
        Livewire::test(ListAchievements::class)
            ->callAction('award', [
                'account_id' => [$account->id],
                'achievements' => [$achievement->id],
            ])
            ->assertHasNoFormErrors();

        // Assert that only one record exists in the database
        $this->assertDatabaseCount('mship_achievement_award', 1);
    }

    public function test_awarding_a_soft_deleted_achievement_restores_it()
    {
        $this->actingAsAdminUser(['achievements.view', 'achievements.award']);

        $account = Account::factory()->create();
        $achievement = Achievement::factory()->create();

        // Award the achievement for the first time
        Livewire::test(ListAchievements::class)
            ->callAction('award', [
                'account_id' => [$account->id],
                'achievements' => [$achievement->id],
            ])
            ->assertHasNoFormErrors();

        // Soft delete the achievement award
        $award = $account->achievementAwards()->where('achievement_id', $achievement->id)->first();
        $award->delete();

        // Attempt to award the same achievement again
        Livewire::test(ListAchievements::class)
            ->callAction('award', [
                'account_id' => [$account->id],
                'achievements' => [$achievement->id],
            ])
            ->assertHasNoFormErrors();

        // Assert that the soft-deleted record has been restored
        $this->assertDatabaseHas('mship_achievement_award', [
            'id' => $award->id,
            'deleted_at' => null,
        ]);
    }

    public function test_awarded_members_page_only_shows_members_who_have_been_awarded_the_achievement()
    {
        $this->actingAsAdminUser(['achievements.view', 'achievements.award']);

        $achievement = Achievement::factory()->create();
        $otherAchievement = Achievement::factory()->create();

        $awardedAccount = Account::factory()->create();
        $notAwardedAccount = Account::factory()->create();

        // Award the achievement to one account
        $awardedAccount->achievementAwards()->create([
            'achievement_id' => $achievement->id,
            'created_by' => auth()->user()->id,
            'updated_by' => auth()->user()->id,
        ]);

        // Award a different achievement to another account
        $notAwardedAccount->achievementAwards()->create([
            'achievement_id' => $otherAchievement->id,
            'created_by' => auth()->user()->id,
            'updated_by' => auth()->user()->id,
        ]);

        Livewire::test(AwardedMembers::class, ['record' => $achievement])
            ->assertSee($awardedAccount->name)
            ->assertDontSee($notAwardedAccount->name);
    }

    public function test_it_can_remove_award_from_member()
    {
        $this->actingAsAdminUser(['achievements.view', 'achievements.award']);

        $achievement = Achievement::factory()->create();
        $account = Account::factory()->create();

        // Award the achievement to the account
        $award = $account->achievementAwards()->create([
            'achievement_id' => $achievement->id,
            'created_by' => auth()->user()->id,
            'updated_by' => auth()->user()->id,
        ]);

        Livewire::test(AwardedMembers::class, ['record' => $achievement])
            ->callAction(TestAction::make('remove')->table($award))
            ->assertHasNoFormErrors();

        // Assert that the award has been soft deleted
        $this->assertSoftDeleted('mship_achievement_award', [
            'id' => $award->id,
        ]);
    }
}
