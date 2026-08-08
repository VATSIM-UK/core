<?php

namespace Tests\Feature\TrainingPanel\WaitingLists\Pages;

use App\Enums\TrainingPlaceOfferStatus;
use App\Filament\Training\Resources\WaitingLists\RelationManagers\AccountsRelationManager;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class QualificationTrainingPlaceFromWaitingListTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    private function pilotQualification(): Qualification
    {
        return Qualification::firstWhere('code', 'PPL')
            ?? Qualification::factory()->create(['code' => 'PPL', 'type' => 'pilot']);
    }

    private function pilotWaitingListWithQualification(Qualification $qualification): WaitingList
    {
        $waitingList = WaitingList::factory()->create([
            'department' => WaitingList::PILOT_DEPARTMENT,
        ]);
        $waitingList->qualifications()->attach($qualification);

        return $waitingList;
    }

    #[Test]
    public function it_can_offer_a_qualification_backed_training_place_from_a_pilot_list(): void
    {
        Notification::fake();

        $qualification = $this->pilotQualification();
        $waitingList = $this->pilotWaitingListWithQualification($qualification);

        $student = Account::factory()->create();
        Member::factory()->create(['cid' => $student->id]);
        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        Livewire::actingAs($this->privacc)
            ->test(AccountsRelationManager::class, [
                'ownerRecord' => $waitingList,
                'pageClass' => ViewRecord::class,
            ])
            ->callTableAction('offerTrainingPlace', $waitingListAccount, [
                'trainable' => Qualification::class.'|'.$qualification->id,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('training_place_offers', [
            'waiting_list_account_id' => $waitingListAccount->id,
            'trainable_type' => Qualification::class,
            'trainable_id' => $qualification->id,
            'status' => TrainingPlaceOfferStatus::Pending->value,
        ]);

        $offer = $waitingListAccount->trainingPlaceOffers()->latest()->first();
        $this->assertNotNull($offer);
        $this->assertSame(WaitingList::PILOT_DEPARTMENT, $offer->department);
    }

    #[Test]
    public function it_can_manual_setup_a_qualification_backed_training_place_from_a_pilot_list(): void
    {
        $qualification = $this->pilotQualification();
        $waitingList = $this->pilotWaitingListWithQualification($qualification);

        $student = Account::factory()->create();
        Member::factory()->create(['cid' => $student->id]);
        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        Livewire::actingAs($this->privacc)
            ->test(AccountsRelationManager::class, [
                'ownerRecord' => $waitingList,
                'pageClass' => ViewRecord::class,
            ])
            ->callTableAction('manualSetupTrainingPlace', $waitingListAccount, [
                'trainable' => Qualification::class.'|'.$qualification->id,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('training_places', [
            'waiting_list_account_id' => $waitingListAccount->id,
            'trainable_type' => Qualification::class,
            'trainable_id' => $qualification->id,
        ]);

        $trainingPlace = TrainingPlace::query()
            ->where('waiting_list_account_id', $waitingListAccount->id)
            ->first();

        $this->assertNotNull($trainingPlace);
        $this->assertSame(WaitingList::PILOT_DEPARTMENT, $trainingPlace->department);
        $this->assertNotNull($waitingListAccount->fresh()->deleted_at);
    }

    #[Test]
    public function it_rejects_a_trainable_that_is_not_attached_to_the_waiting_list(): void
    {
        $attachedQualification = $this->pilotQualification();
        $unattachedQualification = Qualification::firstWhere('code', 'IR')
            ?? Qualification::factory()->create(['code' => 'IR', 'type' => 'pilot']);

        $waitingList = $this->pilotWaitingListWithQualification($attachedQualification);

        $student = Account::factory()->create();
        Member::factory()->create(['cid' => $student->id]);
        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        Livewire::actingAs($this->privacc)
            ->test(AccountsRelationManager::class, [
                'ownerRecord' => $waitingList,
                'pageClass' => ViewRecord::class,
            ])
            ->callTableAction('manualSetupTrainingPlace', $waitingListAccount, [
                'trainable' => Qualification::class.'|'.$unattachedQualification->id,
            ])
            ->assertHasTableActionErrors(['trainable']);

        $this->assertDatabaseMissing('training_places', [
            'waiting_list_account_id' => $waitingListAccount->id,
        ]);
    }

    #[Test]
    public function it_rejects_an_unattached_training_position_on_offer(): void
    {
        Notification::fake();

        $trainingPosition = TrainingPosition::factory()->create();
        $waitingList = WaitingList::factory()->create();
        // Position deliberately not attached to the list

        $student = Account::factory()->create();
        Member::factory()->create(['cid' => $student->id]);
        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        Livewire::actingAs($this->privacc)
            ->test(AccountsRelationManager::class, [
                'ownerRecord' => $waitingList,
                'pageClass' => ViewRecord::class,
            ])
            ->callTableAction('offerTrainingPlace', $waitingListAccount, [
                'trainable' => TrainingPosition::class.'|'.$trainingPosition->id,
            ])
            ->assertHasTableActionErrors(['trainable']);

        $this->assertDatabaseMissing('training_place_offers', [
            'waiting_list_account_id' => $waitingListAccount->id,
        ]);
    }
}
