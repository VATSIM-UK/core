<?php

declare(strict_types=1);

namespace Tests\Unit\Training\Mentoring;

use App\Enums\PositionValidationStatusEnum;
use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Cts\PositionValidation;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Services\Training\MentorPermissionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MentorPermissionServiceTest extends TestCase
{
    use DatabaseTransactions;

    private MentorPermissionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(MentorPermissionService::class);
    }

    #[Test]
    public function it_assigns_atc_permissions_and_syncs_cts_validations(): void
    {
        $actor = Account::factory()->create();
        $mentor = $this->createAccountWithMember();
        $category = 'OBS to S1 Training';

        $trainingPosition = $this->createTrainingPosition($category, ['EGLL_GND']);

        $this->service->assignToMentorable($mentor, $trainingPosition, $actor, $category);

        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $mentor->id,
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => $trainingPosition->id,
            'created_by' => $actor->id,
        ]);

        $this->assertDatabaseHas('position_validations', [
            'member_id' => $mentor->member->id,
            'position_id' => CtsPosition::where('callsign', 'EGLL_GND')->firstOrFail()->id,
            'status' => PositionValidationStatusEnum::Mentor->value,
        ], 'cts');

        $this->assertTrue($mentor->fresh()->hasRole('ATC Mentor (OBS)'));
    }

    #[Test]
    public function it_assigns_pilot_permissions_and_syncs_cts_validations(): void
    {
        $actor = Account::factory()->create();
        $mentor = $this->createAccountWithMember();
        $category = 'P1 Training';

        CtsPosition::firstOrCreate(['callsign' => 'P1_MENTOR']);
        $qualification = $this->getOrCreateQualification('PPL');

        $this->service->assignToMentorable($mentor, $qualification, $actor, $category);

        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $mentor->id,
            'mentorable_type' => Qualification::class,
            'mentorable_id' => $qualification->id,
            'created_by' => $actor->id,
        ]);

        $this->assertDatabaseHas('position_validations', [
            'member_id' => $mentor->member->id,
            'position_id' => CtsPosition::where('callsign', 'P1_MENTOR')->firstOrFail()->id,
            'status' => PositionValidationStatusEnum::Mentor->value,
        ], 'cts');

        $this->assertTrue($mentor->fresh()->hasRole('Pilot Mentor'));
    }

    #[Test]
    public function it_does_not_create_duplicate_permissions_or_cts_validations(): void
    {
        $actor = Account::factory()->create();
        $mentor = $this->createAccountWithMember();

        $trainingPosition = $this->createTrainingPosition('S2 Training', ['EGPH_APP']);

        $this->service->assignToMentorable($mentor, $trainingPosition, $actor, 'S2 Training');
        $this->service->assignToMentorable($mentor, $trainingPosition, $actor, 'S2 Training');

        $this->assertSame(
            1,
            MentorTrainingPosition::where('account_id', $mentor->id)
                ->where('mentorable_type', TrainingPosition::class)
                ->where('mentorable_id', $trainingPosition->id)
                ->count()
        );

        $this->assertSame(
            1,
            PositionValidation::where('member_id', $mentor->member->id)
                ->where('position_id', CtsPosition::where('callsign', 'EGPH_APP')->firstOrFail()->id)
                ->where('status', PositionValidationStatusEnum::Mentor->value)
                ->count()
        );
    }

    #[Test]
    public function it_syncs_positions_only_within_the_selected_category(): void
    {
        $actor = Account::factory()->create();
        $mentor = $this->createAccountWithMember();

        $selectedCategory = 'S3 Training';

        $toRemove = $this->createTrainingPosition($selectedCategory, ['EGCC_GND']);
        $toKeep = $this->createTrainingPosition($selectedCategory, ['EGCC_TWR']);
        $toAdd = $this->createTrainingPosition($selectedCategory, ['EGCC_APP']);

        $otherCategoryPosition = $this->createTrainingPosition('Heathrow GMC', ['EGLL_GMC']);

        $this->service->assignToMentorable($mentor, $toRemove, $actor, $selectedCategory);
        $this->service->assignToMentorable($mentor, $toKeep, $actor, $selectedCategory);
        $this->service->assignToMentorable($mentor, $otherCategoryPosition, $actor, 'Heathrow GMC');

        $this->service->syncPositionsInCategory(
            $mentor,
            $selectedCategory,
            collect([$toKeep->id, $toAdd->id]),
            $actor
        );

        $this->assertDatabaseMissing('mentor_training_positions', [
            'account_id' => $mentor->id,
            'mentorable_id' => $toRemove->id,
        ]);
        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $mentor->id,
            'mentorable_id' => $toKeep->id,
        ]);
        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $mentor->id,
            'mentorable_id' => $toAdd->id,
        ]);
        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $mentor->id,
            'mentorable_id' => $otherCategoryPosition->id,
        ]);
    }

    #[Test]
    public function it_revokes_permissions_only_for_the_selected_category(): void
    {
        $actor = Account::factory()->create();
        $mentor = $this->createAccountWithMember();

        $selectedCategory = 'C1 Training';
        $otherCategory = 'Heathrow AIR';

        $selectedTrainingPosition = $this->createTrainingPosition($selectedCategory, ['EGKK_CTR']);
        $otherTrainingPosition = $this->createTrainingPosition($otherCategory, ['EGLL_APP']);

        $this->service->assignToMentorable($mentor, $selectedTrainingPosition, $actor, $selectedCategory);
        $this->service->assignToMentorable($mentor, $otherTrainingPosition, $actor, $otherCategory);

        $this->assertTrue($mentor->fresh()->hasRole('ATC Mentor (ENR)'));
        $this->assertTrue($mentor->fresh()->hasRole('ATC Mentor (Heathrow)'));

        $this->service->revokeFromCategory($mentor, $selectedCategory);

        $this->assertDatabaseMissing('mentor_training_positions', [
            'account_id' => $mentor->id,
            'mentorable_id' => $selectedTrainingPosition->id,
        ]);

        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $mentor->id,
            'mentorable_id' => $otherTrainingPosition->id,
        ]);

        $this->assertFalse($mentor->fresh()->hasRole('ATC Mentor (ENR)'));
        $this->assertTrue($mentor->fresh()->hasRole('ATC Mentor (Heathrow)'));
    }

    #[Test]
    public function it_removes_the_mentor_role_when_no_permissions_remain_in_the_type(): void
    {
        $actor = Account::factory()->create();
        $mentor = $this->createAccountWithMember();
        $category = 'S2 Training';

        $trainingPosition = $this->createTrainingPosition($category, ['EGPH_APP']);

        $this->service->assignToMentorable($mentor, $trainingPosition, $actor, $category);
        $this->assertTrue($mentor->fresh()->hasRole('ATC Mentor (TWR)'));

        $this->service->revokeFromCategory($mentor, $category);

        $this->assertFalse($mentor->fresh()->hasRole('ATC Mentor (TWR)'));
    }

    #[Test]
    public function it_removes_cts_validations_when_atc_permissions_are_revoked(): void
    {
        $actor = Account::factory()->create();
        $mentor = $this->createAccountWithMember();
        $category = 'S2 Training';
        $callsign = 'EGCC_TWR';

        $trainingPosition = $this->createTrainingPosition($category, [$callsign]);
        $this->service->assignToMentorable($mentor, $trainingPosition, $actor, $category);

        $ctsPosition = CtsPosition::where('callsign', $callsign)->firstOrFail();
        $this->assertDatabaseHas('position_validations', [
            'member_id' => $mentor->member->id,
            'position_id' => $ctsPosition->id,
            'status' => PositionValidationStatusEnum::Mentor->value,
        ], 'cts');

        $this->service->revokeFromCategory($mentor, $category);

        $this->assertDatabaseMissing('mentor_training_positions', [
            'account_id' => $mentor->id,
            'mentorable_id' => $trainingPosition->id,
        ]);

        $this->assertDatabaseMissing('position_validations', [
            'member_id' => $mentor->member->id,
            'position_id' => $ctsPosition->id,
        ], 'cts');
    }

    #[Test]
    #[DataProvider('atcCategoryRoleProvider')]
    public function it_assigns_the_expected_role_for_each_atc_category(string $category, string $expectedRole): void
    {
        $actor = Account::factory()->create();
        $mentor = $this->createAccountWithMember();

        $trainingPosition = $this->createTrainingPosition($category, ['EGXX_APP']);

        $this->service->assignToMentorable($mentor, $trainingPosition, $actor, $category);

        $this->assertTrue($mentor->fresh()->hasRole($expectedRole));
    }

    #[Test]
    #[DataProvider('pilotCategoryRoleProvider')]
    public function it_assigns_the_expected_role_for_each_pilot_category(string $category, string $expectedRole, string $qualCode, string $ctsCallsign): void
    {
        $actor = Account::factory()->create();
        $mentor = $this->createAccountWithMember();

        CtsPosition::firstOrCreate(['callsign' => $ctsCallsign]);
        $qualification = $this->getOrCreateQualification($qualCode);

        $this->service->assignToMentorable($mentor, $qualification, $actor, $category);

        $this->assertTrue($mentor->fresh()->hasRole($expectedRole));
    }

    #[Test]
    public function it_keeps_a_shared_role_if_another_category_with_the_same_role_still_has_permissions(): void
    {
        $actor = Account::factory()->create();
        $mentor = $this->createAccountWithMember();
        $categoryOne = 'Heathrow GMC';
        $categoryTwo = 'Heathrow AIR';
        $sharedRole = MentorPermissionService::ATC_CATEGORY_ROLE_MAP[$categoryOne];

        $positionOne = $this->createTrainingPosition($categoryOne, ['EGLL_GMC']);
        $positionTwo = $this->createTrainingPosition($categoryTwo, ['EGLL_APP']);

        $this->service->assignToMentorable($mentor, $positionOne, $actor, $categoryOne);
        $this->service->assignToMentorable($mentor, $positionTwo, $actor, $categoryTwo);

        $this->assertTrue($mentor->fresh()->hasRole($sharedRole));

        $this->service->revokeFromCategory($mentor, $categoryOne);

        $this->assertTrue($mentor->fresh()->hasRole($sharedRole));
    }

    private function createAccountWithMember(): Account
    {
        $account = Account::factory()->create();
        Member::factory()->create(['cid' => $account->id]);

        return $account->fresh();
    }

    private function createTrainingPosition(string $category, array $ctsCallsigns): TrainingPosition
    {
        foreach ($ctsCallsigns as $callsign) {
            CtsPosition::firstOrCreate(['callsign' => $callsign]);
        }

        return TrainingPosition::factory()->create([
            'category' => $category,
            'cts_positions' => $ctsCallsigns,
        ]);
    }

    private function getOrCreateQualification(string $code): Qualification
    {
        return Qualification::firstWhere('code', $code) ?? Qualification::factory()->create(['code' => $code, 'type' => 'pilot']);
    }

    public static function atcCategoryRoleProvider(): array
    {
        return collect(MentorPermissionService::ATC_CATEGORY_ROLE_MAP)
            ->map(fn (string $role, string $category) => [$category, $role])
            ->values()
            ->all();
    }

    public static function pilotCategoryRoleProvider(): array
    {
        return collect(MentorPermissionService::PILOT_CATEGORY_ROLE_MAP)
            ->map(function (string $role, string $category) {
                $code = MentorPermissionService::PILOT_CATEGORY_QUALIFICATION_MAP[$category];
                $ctsCallsign = MentorPermissionService::QUALIFICATION_CTS_POSITION_MAP[$code];

                return [$category, $role, $code, $ctsCallsign];
            })
            ->values()
            ->all();
    }
}
