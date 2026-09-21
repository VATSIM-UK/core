<?php

namespace Tests\Feature\Training\EndorsementRequest;

use App\Filament\Training\Pages\Endorsements\Tables\ResourceTable;
use App\Filament\Training\Resources\EndorsementRequests\EndorsementRequestResource;
use App\Models\Atc\PositionGroup;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Mship\Account\EndorsementRequest;
use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use Livewire\Livewire;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class EndorsementRequestVisibilityTest extends BaseTrainingPanelTestCase
{
    protected TrainingPosition $mentoredPosition;

    protected TrainingPosition $otherTrainingGroupPosition;

    protected Account $mentor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mentoredPosition = $this->createTrainingPosition('EGLL_APP', 'S3 Training');
        $this->otherTrainingGroupPosition = $this->createTrainingPosition('EGTT_CTR', 'C1 Training');

        $this->mentor = $this->createMemberAccount([
            'training.access',
            'endorsement-request.access',
            'endorsement-request.create.*',
            'endorsement-request.view.*',
        ]);

        MentorTrainingPosition::query()->create([
            'account_id' => $this->mentor->id,
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => $this->mentoredPosition->id,
            'created_by' => $this->mentor->id,
        ]);
    }

    public function test_mentor_sees_requests_for_students_in_their_training_group(): void
    {
        $endorsementRequest = $this->createRequestFor($this->createStudentOn($this->mentoredPosition));

        Livewire::actingAs($this->mentor);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$endorsementRequest]);
    }

    public function test_mentor_does_not_see_requests_for_students_outside_their_training_group(): void
    {
        $endorsementRequest = $this->createRequestFor($this->createStudentOn($this->otherTrainingGroupPosition));

        Livewire::actingAs($this->mentor);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertSuccessful()
            ->assertCanNotSeeTableRecords([$endorsementRequest]);
    }

    public function test_mentor_sees_requests_they_raised_for_students_outside_their_training_group(): void
    {
        $endorsementRequest = $this->createRequestFor(
            $this->createStudentOn($this->otherTrainingGroupPosition),
            $this->mentor,
        );

        Livewire::actingAs($this->mentor);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$endorsementRequest]);
    }

    public function test_mentor_without_a_training_group_sees_only_their_own_requests(): void
    {
        $mentorWithoutPositions = $this->createMemberAccount([
            'training.access',
            'endorsement-request.access',
            'endorsement-request.create.*',
            'endorsement-request.view.*',
        ]);

        $student = $this->createStudentOn($this->mentoredPosition);
        $requestFromSomeoneElse = $this->createRequestFor($student, $this->mentor);
        $ownRequest = $this->createRequestFor($student, $mentorWithoutPositions);

        Livewire::actingAs($mentorWithoutPositions);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$ownRequest])
            ->assertCanNotSeeTableRecords([$requestFromSomeoneElse]);
    }

    public function test_requests_for_students_without_a_training_place_are_hidden_from_mentors(): void
    {
        $student = $this->createMemberAccount();
        $endorsementRequest = $this->createRequestFor($student);

        Livewire::actingAs($this->mentor);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertSuccessful()
            ->assertCanNotSeeTableRecords([$endorsementRequest]);
    }

    public function test_requests_for_students_without_a_training_place_are_visible_to_the_member_who_raised_them(): void
    {
        $endorsementRequest = $this->createRequestFor($this->createMemberAccount(), $this->mentor);

        Livewire::actingAs($this->mentor);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$endorsementRequest]);
    }

    public function test_approvers_see_requests_from_every_training_group(): void
    {
        $approver = $this->createMemberAccount([
            'training.access',
            'endorsement-request.access',
            'endorsement-request.approve.*',
        ]);

        $requestWithoutTrainingPlace = $this->createRequestFor($this->createMemberAccount());
        $requestOutsideTheirTrainingGroup = $this->createRequestFor($this->createStudentOn($this->otherTrainingGroupPosition));

        Livewire::actingAs($approver);
        Livewire::test(ResourceTable::class, ['resource' => EndorsementRequestResource::class])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$requestWithoutTrainingPlace, $requestOutsideTheirTrainingGroup]);
    }

    public function test_resource_query_only_contains_requests_in_scope(): void
    {
        $visibleRequest = $this->createRequestFor($this->createStudentOn($this->mentoredPosition));
        $hiddenRequest = $this->createRequestFor($this->createStudentOn($this->otherTrainingGroupPosition));

        $this->actingAs($this->mentor);

        $visibleIds = EndorsementRequestResource::getEloquentQuery()->pluck('id')->all();

        $this->assertContains($visibleRequest->id, $visibleIds);
        $this->assertNotContains($hiddenRequest->id, $visibleIds);
    }

    public function test_policy_allows_viewing_requests_in_scope(): void
    {
        $endorsementRequest = $this->createRequestFor($this->createStudentOn($this->mentoredPosition));

        $this->assertTrue($this->mentor->can('view', $endorsementRequest));
    }

    public function test_policy_denies_viewing_requests_outside_the_members_scope(): void
    {
        $endorsementRequest = $this->createRequestFor($this->createStudentOn($this->otherTrainingGroupPosition));

        $this->assertFalse($this->mentor->can('view', $endorsementRequest));
    }

    public function test_policy_allows_approvers_to_view_any_request(): void
    {
        $approver = $this->createMemberAccount([
            'endorsement-request.access',
            'endorsement-request.view.*',
            'endorsement-request.approve.*',
        ]);

        $endorsementRequest = $this->createRequestFor($this->createStudentOn($this->otherTrainingGroupPosition));

        $this->assertTrue($approver->can('view', $endorsementRequest));
    }

    private function createMemberAccount(array $permissions = []): Account
    {
        $account = Account::factory()->create();
        Member::factory()->forAccount($account)->create();

        if (filled($permissions)) {
            $account->givePermissionTo($permissions);
        }

        return $account;
    }

    private function createTrainingPosition(string $callsign, string $category): TrainingPosition
    {
        return TrainingPosition::factory()->create([
            'category' => $category,
            'cts_positions' => [$callsign],
            'cts_primary_position' => $callsign,
        ]);
    }

    private function createStudentOn(TrainingPosition $trainingPosition): Account
    {
        $student = $this->createMemberAccount();

        TrainingPlace::withoutEvents(function () use ($student, $trainingPosition): void {
            TrainingPlace::factory()->forTrainingPosition($trainingPosition)->create([
                'account_id' => $student->id,
            ]);
        });

        return $student;
    }

    private function createRequestFor(Account $student, ?Account $requester = null): EndorsementRequest
    {
        return EndorsementRequest::factory()->create([
            'account_id' => $student->id,
            'requested_by' => $requester?->id ?? $this->createMemberAccount()->id,
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => PositionGroup::factory()->create()->id,
        ]);
    }
}
