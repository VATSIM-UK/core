<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel\Mentor;

use App\Enums\PositionValidationStatusEnum;
use App\Filament\Training\Pages\Mentor\ManageMentors;
use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Mship\Account;
use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Services\Training\MentorPermissionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class ManageMentorsTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_loads_when_user_has_atc_view_permission(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        Livewire::actingAs($this->panelUser)
            ->test(ManageMentors::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_is_forbidden_when_user_has_no_view_permission(): void
    {
        Livewire::actingAs($this->panelUser)
            ->test(ManageMentors::class)
            ->assertForbidden();
    }

    #[Test]
    public function it_defaults_to_the_first_visible_category_when_category_is_missing_or_invalid(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        Livewire::actingAs($this->panelUser)
            ->test(ManageMentors::class)
            ->assertSet('category', MentorPermissionService::atcCategories()[0]);

        Livewire::actingAs($this->panelUser)
            ->test(ManageMentors::class, ['category' => 'Invalid Category'])
            ->assertSet('category', MentorPermissionService::atcCategories()[0]);
    }

    #[Test]
    public function it_lists_only_mentors_for_the_selected_category(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $selectedCategory = MentorPermissionService::atcCategories()[0];
        $otherCategory = MentorPermissionService::atcCategories()[1];

        $selectedMentor = Account::factory()->create();
        $otherMentor = Account::factory()->create();
        $actor = Account::factory()->create();

        $callsign1 = 'EGLL_GND';
        $callsign2 = 'EGLL_TWR';

        $selectedTrainingPosition = $this->createTrainingPosition($selectedCategory, $callsign1);
        $otherTrainingPosition = $this->createTrainingPosition($otherCategory, $callsign2);

        MentorTrainingPosition::create([
            'account_id' => $selectedMentor->id,
            'training_position_id' => $selectedTrainingPosition->id,
            'created_by' => $actor->id,
        ]);
        MentorTrainingPosition::create([
            'account_id' => $otherMentor->id,
            'training_position_id' => $otherTrainingPosition->id,
            'created_by' => $actor->id,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ManageMentors::class, ['category' => $selectedCategory])
            ->assertCanSeeTableRecords([$selectedMentor])
            ->assertCanNotSeeTableRecords([$otherMentor]);
    }

    #[Test]
    public function it_hides_manage_actions_without_manage_permission(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $mentor = Account::factory()->create();
        $actor = Account::factory()->create();
        $trainingPosition = $this->createTrainingPosition(MentorPermissionService::atcCategories()[0], 'EGCC_GND');

        MentorTrainingPosition::create([
            'account_id' => $mentor->id,
            'training_position_id' => $trainingPosition->id,
            'created_by' => $actor->id,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ManageMentors::class)
            ->assertTableActionHidden('addMentor')
            ->assertTableActionHidden('managePositions', $mentor)
            ->assertTableActionHidden('removeAll', $mentor);
    }

    #[Test]
    public function it_shows_manage_actions_with_manage_permission(): void
    {
        $this->panelUser->givePermissionTo([
            'training.mentors.view.atc',
            'training.mentors.manage.atc',
        ]);

        $mentor = Account::factory()->create();
        $actor = Account::factory()->create();
        $trainingPosition = $this->createTrainingPosition(MentorPermissionService::atcCategories()[0], 'EGCC_TWR');

        MentorTrainingPosition::create([
            'account_id' => $mentor->id,
            'training_position_id' => $trainingPosition->id,
            'created_by' => $actor->id,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ManageMentors::class)
            ->assertTableActionVisible('addMentor')
            ->assertTableActionVisible('managePositions', $mentor)
            ->assertTableActionVisible('removeAll', $mentor);
    }

    #[Test]
    public function it_can_add_a_mentor_with_selected_positions(): void
    {
        $this->panelUser->givePermissionTo([
            'training.mentors.view.atc',
            'training.mentors.manage.atc',
        ]);

        $category = MentorPermissionService::atcCategories()[0];
        $target = Account::factory()->create();
        Member::factory()->create(['cid' => $target->id]);

        $trainingPositionOne = $this->createTrainingPosition($category, 'EGKK_GND');
        $trainingPositionTwo = $this->createTrainingPosition($category, 'EGKK_TWR');

        Livewire::actingAs($this->panelUser)
            ->test(ManageMentors::class, ['category' => $category])
            ->callTableAction('addMentor', data: [
                'account_id' => $target->id,
                'position_ids' => [$trainingPositionOne->id, $trainingPositionTwo->id],
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $target->id,
            'training_position_id' => $trainingPositionOne->id,
            'created_by' => $this->panelUser->id,
        ]);
        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $target->id,
            'training_position_id' => $trainingPositionTwo->id,
            'created_by' => $this->panelUser->id,
        ]);
        $this->assertDatabaseHas('position_validations', [
            'member_id' => $target->member->id,
            'position_id' => CtsPosition::where('callsign', $trainingPositionOne->cts_positions[0])->firstOrFail()->id,
            'status' => PositionValidationStatusEnum::Mentor->value,
        ], 'cts');
        $this->assertDatabaseHas('position_validations', [
            'member_id' => $target->member->id,
            'position_id' => CtsPosition::where('callsign', $trainingPositionTwo->cts_positions[0])->firstOrFail()->id,
            'status' => PositionValidationStatusEnum::Mentor->value,
        ], 'cts');
        $this->assertTrue($target->fresh()->hasRole('ATC Mentor (OBS)'));
    }

    #[Test]
    public function it_can_sync_permissions_for_a_mentor_in_a_category(): void
    {
        $this->panelUser->givePermissionTo([
            'training.mentors.view.atc',
            'training.mentors.manage.atc',
        ]);

        $category = MentorPermissionService::atcCategories()[2];
        $mentor = Account::factory()->create();
        Member::factory()->create(['cid' => $mentor->id]);

        $trainingPositionOne = $this->createTrainingPosition($category, 'EGGD_GND');
        $trainingPositionTwo = $this->createTrainingPosition($category, 'EGGD_TWR');
        $trainingPositionThree = $this->createTrainingPosition($category, 'EGGD_APP');

        app(MentorPermissionService::class)->assignToPositions(
            $mentor,
            collect([$trainingPositionOne, $trainingPositionTwo]),
            $this->panelUser,
            $category
        );

        Livewire::actingAs($this->panelUser)
            ->test(ManageMentors::class, ['category' => $category])
            ->callTableAction('managePositions', $mentor, data: [
                'position_ids' => [$trainingPositionTwo->id, $trainingPositionThree->id],
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('mentor_training_positions', [
            'account_id' => $mentor->id,
            'training_position_id' => $trainingPositionOne->id,
        ]);
        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $mentor->id,
            'training_position_id' => $trainingPositionTwo->id,
        ]);
        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $mentor->id,
            'training_position_id' => $trainingPositionThree->id,
        ]);
    }

    #[Test]
    public function it_can_remove_all_permissions_for_a_mentor_in_the_selected_category_only(): void
    {
        $this->panelUser->givePermissionTo([
            'training.mentors.view.atc',
            'training.mentors.manage.atc',
        ]);

        $selectedCategory = MentorPermissionService::atcCategories()[3];
        $otherCategory = MentorPermissionService::atcCategories()[4];

        $mentor = Account::factory()->create();
        Member::factory()->create(['cid' => $mentor->id]);

        $selectedTrainingPosition = $this->createTrainingPosition($selectedCategory, 'EGNX_CTR');
        $otherTrainingPosition = $this->createTrainingPosition($otherCategory, 'EGLL_GMC');

        app(MentorPermissionService::class)->assignToPositions(
            $mentor,
            collect([$selectedTrainingPosition, $otherTrainingPosition]),
            $this->panelUser,
            $selectedCategory
        );

        Livewire::actingAs($this->panelUser)
            ->test(ManageMentors::class, ['category' => $selectedCategory])
            ->callTableAction('removeAll', $mentor)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('mentor_training_positions', [
            'account_id' => $mentor->id,
            'training_position_id' => $selectedTrainingPosition->id,
        ]);
        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $mentor->id,
            'training_position_id' => $otherTrainingPosition->id,
        ]);
        $this->assertTrue($mentor->fresh()->hasRole('ATC Mentor (Heathrow GMC)'));
    }

    private function createTrainingPosition(string $category, string $callsign): TrainingPosition
    {
        CtsPosition::factory()->create(['callsign' => $callsign]);

        return TrainingPosition::factory()->create([
            'category' => $category,
            'cts_positions' => [$callsign],
        ]);
    }
}
