<?php

namespace Tests\Unit\Training\TrainingPlace;

use App\Enums\PositionValidationStatusEnum;
use App\Enums\TrainingPlaceOfferStatus;
use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use App\Models\Training\WaitingList;
use App\Services\Training\TrainingPlaceOfferService;
use App\Services\Training\TrainingPlaceService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QualificationTrainingPlaceTest extends TestCase
{
    use DatabaseTransactions;

    private function pilotQualification(): Qualification
    {
        return Qualification::firstWhere('code', 'PPL')
            ?? Qualification::factory()->create(['code' => 'PPL', 'type' => 'pilot']);
    }

    #[Test]
    public function it_creates_a_manual_training_place_associated_with_a_qualification(): void
    {
        $this->actingAs($this->privacc);

        $qualification = $this->pilotQualification();
        $waitingList = WaitingList::factory()->create();
        $student = Account::factory()->create();
        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        $trainingPlace = TrainingPlace::withoutEvents(function () use ($waitingListAccount, $qualification) {
            return (new TrainingPlaceService)->createManualTrainingPlace($waitingListAccount, $qualification);
        });

        $this->assertEquals($qualification->id, $trainingPlace->trainable_id);
        $this->assertInstanceOf(Qualification::class, $trainingPlace->trainable);
        $this->assertNull($trainingPlace->trainingPosition);
        $this->assertTrue($trainingPlace->qualification->is($qualification));
        $this->assertDatabaseHas('training_places', [
            'id' => $trainingPlace->id,
            'trainable_type' => Qualification::class,
            'trainable_id' => $qualification->id,
        ]);
    }

    #[Test]
    public function it_creates_an_adhoc_training_place_associated_with_a_qualification(): void
    {
        $qualification = $this->pilotQualification();
        $student = Account::factory()->create();
        $reason = 'This is a valid reason for creating an ad-hoc training place.';

        $trainingPlace = TrainingPlace::withoutEvents(function () use ($student, $qualification, $reason) {
            return (new TrainingPlaceService)->createAdhocTrainingPlace($student, $qualification, $reason, $this->privacc);
        });

        $this->assertEquals($qualification->id, $trainingPlace->trainable_id);
        $this->assertInstanceOf(Qualification::class, $trainingPlace->trainable);
        $this->assertDatabaseHas('mship_account_note', [
            'account_id' => $student->id,
            'writer_id' => $this->privacc->id,
            'content' => "Ad-hoc training place created on {$trainingPlace->display_name} outside the usual waiting list flow. Reason: {$reason}",
        ]);
    }

    #[Test]
    public function it_assigns_mentoring_permissions_using_the_mapped_cts_position_for_a_qualification(): void
    {
        $ctsPosition = CtsPosition::firstOrCreate(['callsign' => 'P1_PPL(A)']);
        $qualification = $this->pilotQualification();

        $student = Account::factory()->create();
        Member::factory()->create(['cid' => $student->id]);

        $trainingPlace = TrainingPlace::withoutEvents(function () use ($qualification, $student) {
            return TrainingPlace::factory()
                ->forQualification($qualification)
                ->create(['account_id' => $student->id]);
        });

        (new TrainingPlaceService)->assignMentoringPermissions($trainingPlace);

        $this->assertDatabaseHas('position_validations', [
            'member_id' => $student->member->id,
            'position_id' => $ctsPosition->id,
            'status' => PositionValidationStatusEnum::Student->value,
        ], 'cts');
    }

    #[Test]
    public function it_offers_and_accepts_a_qualification_backed_training_place(): void
    {
        Notification::fake();
        $this->actingAs($this->privacc);

        $qualification = $this->pilotQualification();
        $waitingList = WaitingList::factory()->create();
        $student = Account::factory()->create();
        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        $service = new TrainingPlaceOfferService;
        $service->offerTrainingPlace($waitingListAccount, $qualification);

        $this->assertDatabaseHas('training_place_offers', [
            'waiting_list_account_id' => $waitingListAccount->id,
            'trainable_type' => Qualification::class,
            'trainable_id' => $qualification->id,
            'status' => TrainingPlaceOfferStatus::Pending->value,
        ]);

        $offer = TrainingPlaceOffer::where('waiting_list_account_id', $waitingListAccount->id)->firstOrFail();

        TrainingPlace::withoutEvents(fn () => $service->acceptOffer($offer));

        $this->assertDatabaseHas('training_places', [
            'waiting_list_account_id' => $waitingListAccount->id,
            'trainable_type' => Qualification::class,
            'trainable_id' => $qualification->id,
        ]);
    }
}
