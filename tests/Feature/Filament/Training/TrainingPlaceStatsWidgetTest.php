<?php

namespace Tests\Feature\Filament\Training;

use App\Filament\Training\Pages\TrainingPlace\Widgets\TrainingPlaceStatsWidget;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrainingPlaceStatsWidgetTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_shows_na_for_waiting_time_when_no_waiting_list_account_is_present(): void
    {
        $student = Account::factory()->create();
        $trainingPosition = TrainingPosition::factory()->create();

        $trainingPlace = TrainingPlace::factory()->create([
            'account_id' => $student->id,
            'training_position_id' => $trainingPosition->id,
            'waiting_list_account_id' => null,
        ]);

        Livewire::test(TrainingPlaceStatsWidget::class, ['trainingPlace' => $trainingPlace])
            ->assertSee('Waiting Time in Queue')
            ->assertSee('N/A');
    }

    #[Test]
    public function it_calculates_waiting_time_using_now_when_waiting_list_account_is_still_active(): void
    {
        $this->actingAs($this->privacc);

        $trainingPosition = TrainingPosition::factory()->create();
        $waitingList = WaitingList::factory()->create();
        $student = Account::factory()->create();

        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);
        $waitingListAccount->forceFill([
            'created_at' => now()->subDay(),
            'deleted_at' => null,
        ])->save();

        $trainingPlace = TrainingPlace::factory()->create([
            'account_id' => $student->id,
            'training_position_id' => $trainingPosition->id,
            'waiting_list_account_id' => $waitingListAccount->id,
        ]);

        Livewire::test(TrainingPlaceStatsWidget::class, ['trainingPlace' => $trainingPlace])
            ->assertSee('Waiting Time in Queue')
            ->assertSee('1 day');
    }
}
