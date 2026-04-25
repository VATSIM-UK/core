<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel\Mentor;

use App\Enums\PositionValidationStatusEnum;
use App\Filament\Training\Pages\Mentor\ManageMentors;
use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
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
    public function it_lists_only_mentors_for_the_selected_atc_category(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $selectedCategory = MentorPermissionService::atcCategories()[0];
        $otherCategory = MentorPermissionService::atcCategories()[1];

        $selectedMentor = Account::factory()->create();
        $otherMentor = Account::factory()->create();
        $actor = Account::factory()->create();

        $selectedTrainingPosition = $this->createTrainingPosition($selectedCategory, 'EGLL_GND');
        $otherTrainingPosition = $this->createTrainingPosition($otherCategory, 'EGLL_TWR');

        MentorTrainingPosition::create([
            'account_id' => $selectedMentor->id,
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => $selectedTrainingPosition->id,
            'created_by' => $actor->id,
        ]);

        MentorTrainingPosition::create([
            'account_id' => $otherMentor->id,
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => $otherTrainingPosition->id,
            'created_by' => $actor->id,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ManageMentors::class, ['category' => $selectedCategory])
            ->assertCanSeeTableRecords([$selectedMentor])
            ->assertCanNotSeeTableRecords([$otherMentor]);
    }

    #[Test]
    public function it_lists_only_mentors_for_the_selected_pilot_category(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.pilot');

        $selectedCategory = 'P1 Training';
        $otherCategory = 'P2 Training';

        $selectedMentor = Account::factory()->create();
        $otherMentor = Account::factory()->create();
        $actor = Account::factory()->create();

        $pplQual = $this->getOrCreateQualification('PPL');
        $irQual = $this->getOrCreateQualification('IR');

        MentorTrainingPosition::create([
            'account_id' => $selectedMentor->id,
            'mentorable_type' => Qualification::class,
            'mentorable_id' => $pplQual->id,
            'created_by' => $actor->id,
        ]);

        MentorTrainingPosition::create([
            'account_id' => $otherMentor->id,
            'mentorable_type' => Qualification::class,
            'mentorable_id' => $irQual->id,
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
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => $trainingPosition->id,
            'created_by' => $actor->id,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ManageMentors::class)
            ->assertTableActionHidden('addMentor')
            ->assertTableActionHidden('managePositions', $mentor)
            ->assertTableActionHidden('removeAll', $mentor);
    }

    #[Test]
    public function it_can_add_an_atc_mentor_with_selected_positions(): void
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
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => $trainingPositionOne->id,
            'created_by' => $this->panelUser->id,
        ]);

        $this->assertDatabaseHas('position_validations', [
            'member_id' => $target->member->id,
            'position_id' => CtsPosition::where('callsign', $trainingPositionOne->cts_positions[0])->firstOrFail()->id,
            'status' => PositionValidationStatusEnum::Mentor->value,
        ], 'cts');

        $this->assertTrue($target->fresh()->hasRole('ATC Mentor (OBS)'));
    }

    #[Test]
    public function it_can_add_a_pilot_mentor_with_selected_qualifications(): void
    {
        $this->panelUser->givePermissionTo([
            'training.mentors.view.pilot',
            'training.mentors.manage.pilot',
        ]);

        $category = 'P1 Training';
        $target = Account::factory()->create();
        Member::factory()->create(['cid' => $target->id]);

        CtsPosition::firstOrCreate(['callsign' => 'P1_MENTOR']);

        $pplQual = $this->getOrCreateQualification('PPL');

        Livewire::actingAs($this->panelUser)
            ->test(ManageMentors::class, ['category' => $category])
            ->callTableAction('addMentor', data: [
                'account_id' => $target->id,
                'position_ids' => [$pplQual->id],
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $target->id,
            'mentorable_type' => Qualification::class,
            'mentorable_id' => $pplQual->id,
            'created_by' => $this->panelUser->id,
        ]);

        $this->assertDatabaseHas('position_validations', [
            'member_id' => $target->member->id,
            'position_id' => CtsPosition::where('callsign', 'P1_MENTOR')->firstOrFail()->id,
            'status' => PositionValidationStatusEnum::Mentor->value,
        ], 'cts');

        $this->assertTrue($target->fresh()->hasRole('Pilot Mentor'));
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

        app(MentorPermissionService::class)->assignToMentorable($mentor, $trainingPositionOne, $this->panelUser, $category);
        app(MentorPermissionService::class)->assignToMentorable($mentor, $trainingPositionTwo, $this->panelUser, $category);

        Livewire::actingAs($this->panelUser)
            ->test(ManageMentors::class, ['category' => $category])
            ->callTableAction('managePositions', $mentor, data: [
                'position_ids' => [$trainingPositionTwo->id, $trainingPositionThree->id],
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('mentor_training_positions', [
            'account_id' => $mentor->id,
            'mentorable_id' => $trainingPositionOne->id,
        ]);
        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $mentor->id,
            'mentorable_id' => $trainingPositionTwo->id,
        ]);
        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $mentor->id,
            'mentorable_id' => $trainingPositionThree->id,
        ]);
    }

    private function createTrainingPosition(string $category, string $callsign): TrainingPosition
    {
        CtsPosition::firstOrCreate(['callsign' => $callsign]);

        return TrainingPosition::factory()->create([
            'category' => $category,
            'cts_positions' => [$callsign],
        ]);
    }

    private function getOrCreateQualification(string $code): Qualification
    {
        return Qualification::firstWhere('code', $code)
            ?? Qualification::factory()->create(['code' => $code, 'type' => 'pilot']);
    }
}
