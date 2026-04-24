<?php

declare(strict_types=1);

namespace Tests\Unit\Training\Mentoring;

use App\Enums\PositionValidationStatusEnum;
use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Cts\PositionValidation;
use App\Models\Mship\Account;
use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Services\Training\MentorPermissionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
    public function it_assigns_permissions_and_syncs_cts_validations(): void
    {
        $actor = Account::factory()->create();
        $mentor = $this->createAccountWithMember();
        $category = 'Obs To S1 Training';

        $callsignOne = $this->makeCallsign('EGLL_GND');
        $callsignTwo = $this->makeCallsign('EGLL_TWR');

        $ctsPositionOne = CtsPosition::factory()->create(['callsign' => $callsignOne]);
        $ctsPositionTwo = CtsPosition::factory()->create(['callsign' => $callsignTwo]);

        $trainingPositionOne = $this->createTrainingPosition($category, [$callsignOne]);
        $trainingPositionTwo = $this->createTrainingPosition($category, [$callsignTwo]);

        $this->service->assignToPositions($mentor, collect([$trainingPositionOne, $trainingPositionTwo]), $actor, 'atc');

        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $mentor->id,
            'training_position_id' => $trainingPositionOne->id,
            'created_by' => $actor->id,
        ]);
        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $mentor->id,
            'training_position_id' => $trainingPositionTwo->id,
            'created_by' => $actor->id,
        ]);

        $this->assertDatabaseHas('position_validations', [
            'member_id' => $mentor->member->id,
            'position_id' => $ctsPositionOne->id,
            'status' => PositionValidationStatusEnum::Mentor->value,
        ], 'cts');
        $this->assertDatabaseHas('position_validations', [
            'member_id' => $mentor->member->id,
            'position_id' => $ctsPositionTwo->id,
            'status' => PositionValidationStatusEnum::Mentor->value,
        ], 'cts');
    }

    #[Test]
    public function it_does_not_create_duplicate_permissions_or_cts_validations(): void
    {
        $actor = Account::factory()->create();
        $mentor = $this->createAccountWithMember();
        $callsign = $this->makeCallsign('EGPH_APP');

        $ctsPosition = CtsPosition::factory()->create(['callsign' => $callsign]);
        $trainingPosition = $this->createTrainingPosition('S2 Training', [$callsign]);

        $this->service->assignToPositions($mentor, collect([$trainingPosition]), $actor, 'atc');
        $this->service->assignToPositions($mentor, collect([$trainingPosition]), $actor, 'atc');

        $this->assertSame(
            1,
            MentorTrainingPosition::where('account_id', $mentor->id)
                ->where('training_position_id', $trainingPosition->id)
                ->count()
        );

        $this->assertSame(
            1,
            PositionValidation::where('member_id', $mentor->member->id)
                ->where('position_id', $ctsPosition->id)
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
        $otherCategory = 'Heathrow GMC';

        $callsignOne = $this->makeCallsign('EGCC_GND');
        $callsignTwo = $this->makeCallsign('EGCC_TWR');
        $callsignThree = $this->makeCallsign('EGCC_APP');
        $otherCallsign = $this->makeCallsign('EGLL_GMC');

        CtsPosition::factory()->create(['callsign' => $callsignOne]);
        CtsPosition::factory()->create(['callsign' => $callsignTwo]);
        CtsPosition::factory()->create(['callsign' => $callsignThree]);
        CtsPosition::factory()->create(['callsign' => $otherCallsign]);

        $toRemove = $this->createTrainingPosition($selectedCategory, [$callsignOne]);
        $toKeep = $this->createTrainingPosition($selectedCategory, [$callsignTwo]);
        $toAdd = $this->createTrainingPosition($selectedCategory, [$callsignThree]);
        $otherCategoryPosition = $this->createTrainingPosition($otherCategory, [$otherCallsign]);

        $this->service->assignToPositions($mentor, collect([$toRemove, $toKeep, $otherCategoryPosition]), $actor, 'atc');

        $this->service->syncPositionsInCategory(
            $mentor,
            $selectedCategory,
            'atc',
            collect([$toKeep->id, $toAdd->id]),
            $actor
        );

        $this->assertDatabaseMissing('mentor_training_positions', [
            'account_id' => $mentor->id,
            'training_position_id' => $toRemove->id,
        ]);
        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $mentor->id,
            'training_position_id' => $toKeep->id,
        ]);
        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $mentor->id,
            'training_position_id' => $toAdd->id,
        ]);
        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $mentor->id,
            'training_position_id' => $otherCategoryPosition->id,
        ]);
    }

    #[Test]
    public function it_revokes_permissions_only_for_the_selected_category(): void
    {
        $actor = Account::factory()->create();
        $mentor = $this->createAccountWithMember();

        $selectedCategory = 'C1 Training';
        $otherCategory = 'Heathrow Air';

        $selectedCallsign = $this->makeCallsign('EGKK_CTR');
        $otherCallsign = $this->makeCallsign('EGLL_APP');

        $selectedCtsPosition = CtsPosition::factory()->create(['callsign' => $selectedCallsign]);
        $otherCtsPosition = CtsPosition::factory()->create(['callsign' => $otherCallsign]);

        $selectedTrainingPosition = $this->createTrainingPosition($selectedCategory, [$selectedCallsign]);
        $otherTrainingPosition = $this->createTrainingPosition($otherCategory, [$otherCallsign]);

        $this->service->assignToPositions($mentor, collect([$selectedTrainingPosition, $otherTrainingPosition]), $actor, 'atc');

        $this->service->revokeFromCategory($mentor, $selectedCategory, 'atc');

        $this->assertDatabaseMissing('mentor_training_positions', [
            'account_id' => $mentor->id,
            'training_position_id' => $selectedTrainingPosition->id,
        ]);
        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $mentor->id,
            'training_position_id' => $otherTrainingPosition->id,
        ]);

        $this->assertDatabaseMissing('position_validations', [
            'member_id' => $mentor->member->id,
            'position_id' => $selectedCtsPosition->id,
            'status' => PositionValidationStatusEnum::Mentor->value,
        ], 'cts');
        $this->assertDatabaseHas('position_validations', [
            'member_id' => $mentor->member->id,
            'position_id' => $otherCtsPosition->id,
            'status' => PositionValidationStatusEnum::Mentor->value,
        ], 'cts');
    }

    private function createAccountWithMember(): Account
    {
        $account = Account::factory()->create();
        Member::factory()->create(['cid' => $account->id]);

        return $account->fresh();
    }

    private function createTrainingPosition(string $category, array $ctsCallsigns): TrainingPosition
    {
        return TrainingPosition::factory()->create([
            'category' => $category,
            'cts_positions' => $ctsCallsigns,
        ]);
    }

    private function makeCallsign(string $base): string
    {
        // Ensure a short, predictable callsign that won't exceed CTS column limits.
        $prefix = strtoupper(substr($base, 0, 7));

        return $prefix.'_'.substr(md5((string) rand()), 0, 4);
    }
}
