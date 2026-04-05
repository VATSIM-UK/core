<?php

namespace Tests\Unit\Training\TrainingPlace;

use App\Enums\TrainingPlaceOfferStatus;
use App\Filament\Training\Resources\TrainingPlaces\Widgets\TrainingPlaceOffersOverview;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrainingPlaceOffersOverviewTest extends TestCase
{
    use DatabaseTransactions;

    private WaitingList $atcWaitingList;

    private WaitingList $pilotWaitingList;

    protected function setUp(): void
    {
        parent::setUp();

        $this->atcWaitingList = WaitingList::factory()->create(['department' => 'atc']);
        $this->pilotWaitingList = WaitingList::factory()->create(['department' => 'pilot']);
    }

    private function createOffer(WaitingList $waitingList, array $attributes = []): TrainingPlaceOffer
    {
        $student = Account::factory()->create();
        Member::factory()->create(['cid' => $student->id]);

        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);
        $trainingPosition = TrainingPosition::factory()->create();

        return TrainingPlaceOffer::factory()->create(array_merge([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
            'status' => TrainingPlaceOfferStatus::Pending,
            'token' => \Illuminate\Support\Str::random(32),
            'expires_at' => now()->addHours(84),
        ], $attributes));
    }

    private function actingAsWithPermission(string ...$permissions): Account
    {
        $account = Account::factory()->create();
        Member::factory()->create(['cid' => $account->id]);
        $account->givePermissionTo($permissions);
        $this->actingAs($account);

        return $account;
    }

    #[Test]
    public function it_renders_the_widget()
    {
        $this->actingAsWithPermission('waiting-lists.training-place.view-offer.atc');

        Livewire::test(TrainingPlaceOffersOverview::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_shows_offers_for_authorised_waiting_lists()
    {
        $this->actingAsWithPermission('waiting-lists.training-place.view-offer.atc');

        $atcOffer = $this->createOffer($this->atcWaitingList);
        $pilotOffer = $this->createOffer($this->pilotWaitingList);

        Livewire::test(TrainingPlaceOffersOverview::class)
            ->assertCanSeeTableRecords([$atcOffer])
            ->assertCanNotSeeTableRecords([$pilotOffer]);
    }

    #[Test]
    public function rescind_action_is_visible_for_pending_offers_with_permission()
    {
        $this->actingAsWithPermission(
            'waiting-lists.training-place.view-offer.atc',
            'waiting-lists.training-place.rescind-offer.atc',
        );

        $offer = $this->createOffer($this->atcWaitingList, ['status' => TrainingPlaceOfferStatus::Pending]);

        Livewire::test(TrainingPlaceOffersOverview::class)
            ->assertTableActionVisible('rescind', $offer);
    }

    #[Test]
    public function rescind_action_is_hidden_for_non_pending_offers()
    {
        $this->actingAsWithPermission(
            'waiting-lists.training-place.view-offer.atc',
            'waiting-lists.training-place.rescind-offer.atc',
        );

        $declinedOffer = $this->createOffer($this->atcWaitingList, ['status' => TrainingPlaceOfferStatus::Declined]);

        Livewire::test(TrainingPlaceOffersOverview::class)
            ->assertTableActionHidden('rescind', $declinedOffer);
    }

    #[Test]
    public function rescind_action_is_hidden_without_permission()
    {
        $this->actingAsWithPermission(
            'waiting-lists.training-place.view-offer.atc',
        );

        $offer = $this->createOffer($this->atcWaitingList, ['status' => TrainingPlaceOfferStatus::Pending]);

        Livewire::test(TrainingPlaceOffersOverview::class)
            ->assertTableActionHidden('rescind', $offer);
    }

    #[Test]
    public function rescind_and_remove_action_is_visible_with_correct_permissions()
    {
        $this->actingAsWithPermission(
            'waiting-lists.training-place.view-offer.atc',
            'waiting-lists.training-place.rescind-offer.atc',
            'waiting-lists.remove-accounts.atc',
        );

        $offer = $this->createOffer($this->atcWaitingList, ['status' => TrainingPlaceOfferStatus::Pending]);

        Livewire::test(TrainingPlaceOffersOverview::class)
            ->assertTableActionVisible('rescindAndRemove', $offer);
    }

    #[Test]
    public function rescind_and_remove_action_is_hidden_without_remove_permission()
    {
        $this->actingAsWithPermission(
            'waiting-lists.training-place.view-offer.atc',
            'waiting-lists.training-place.rescind-offer.atc',
        );

        $offer = $this->createOffer($this->atcWaitingList, ['status' => TrainingPlaceOfferStatus::Pending]);

        Livewire::test(TrainingPlaceOffersOverview::class)
            ->assertTableActionHidden('rescindAndRemove', $offer);
    }
}
