<?php

namespace Tests\Feature\TrainingPanel\MyTraining;

use App\Filament\Training\Pages\MyTraining\MyAvailability;
use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class MyAvailabilityTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected Account $studentAccount;

    protected Member $studentMember;

    protected function setUp(): void
    {
        parent::setUp();

        $this->studentAccount = Account::factory()->create();
        $this->studentMember = Member::factory()->recycle($this->studentAccount)->create([
            'cid' => $this->studentAccount->id,
        ]);

        $this->studentAccount->givePermissionTo('training.access');
    }

    #[Test]
    public function it_loads_for_member_with_training_access(): void
    {
        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_does_not_load_without_training_access(): void
    {
        $noAccessAccount = Account::factory()->create();
        Member::factory()->recycle($noAccessAccount)->create(['cid' => $noAccessAccount->id]);

        $this->actingAs($noAccessAccount)
            ->get('/training/my-availability')
            ->assertNotFound();
    }

    #[Test]
    public function it_only_shows_availability_for_the_authenticated_member(): void
    {
        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => now()->addDay()->toDateString(),
            'from' => '19:00:00',
            'to' => '21:00:00',
            'type' => 'S',
        ]);

        $otherAccount = Account::factory()->create();
        $otherMember = Member::factory()->recycle($otherAccount)->create(['cid' => $otherAccount->id]);

        Availability::factory()->forStudent($otherMember->id)->create([
            'date' => now()->addDays(2)->toDateString(),
            'from' => '19:30:00',
            'to' => '21:30:00',
            'type' => 'S',
        ]);

        $component = Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->assertSuccessful();

        $this->assertCount(1, $component->instance()->getTable()->getRecords());
    }

    #[Test]
    public function it_only_shows_student_availability_type(): void
    {
        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => now()->addDay()->toDateString(),
            'from' => '19:00:00',
            'to' => '21:00:00',
            'type' => 'S',
        ]);

        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => now()->addDays(3)->toDateString(),
            'from' => '20:00:00',
            'to' => '22:00:00',
            'type' => 'M',
        ]);

        $component = Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->assertSuccessful();

        $records = $component->instance()->getTable()->getRecords();

        $this->assertCount(1, $records);
        $this->assertEquals('S', $records->first()->type);
    }

    #[Test]
    public function it_only_shows_future_availability_slots(): void
    {
        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => now()->subDay()->toDateString(),
            'from' => '19:00:00',
            'to' => '21:00:00',
            'type' => 'S',
        ]);

        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => now()->toDateString(),
            'from' => now()->subHour()->format('H:i:s'),
            'to' => now()->addHour()->format('H:i:s'),
            'type' => 'S',
        ]);

        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => now()->addDay()->toDateString(),
            'from' => '19:30:00',
            'to' => '21:30:00',
            'type' => 'S',
        ]);

        $component = Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->assertSuccessful();

        $records = $component->instance()->getTable()->getRecords();

        $this->assertCount(1, $records);
        $this->assertEquals(now()->addDay()->toDateString(), $records->first()->date->toDateString());
    }
}
