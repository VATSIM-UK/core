<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands\Training;

use App\Jobs\Training\ActionExpiredAvailabilityWarningRemoval;
use App\Models\Training\TrainingPlace\AvailabilityCheck;
use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckForExpiredAvailabilityWarningsCommandTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_dispatches_removal_jobs_for_expired_pending_availability_warnings(): void
    {
        Bus::fake();

        $trainingPlace = TrainingPlace::factory()->create();
        $check = AvailabilityCheck::factory()->failed()->create(['training_place_id' => $trainingPlace->id]);
        $expiredWarning = AvailabilityWarning::factory()->pending()->create([
            'training_place_id' => $trainingPlace->id,
            'availability_check_id' => $check->id,
            'expires_at' => now()->subDay(),
        ]);

        $nonExpiredWarning = AvailabilityWarning::factory()->pending()->create([
            'training_place_id' => $trainingPlace->id,
            'availability_check_id' => $check->id,
            'expires_at' => now()->addDays(1),
        ]);

        Artisan::call('training-places:check-for-expired-availability-warnings');

        Bus::assertDispatched(ActionExpiredAvailabilityWarningRemoval::class, function ($job) use ($expiredWarning) {
            return $job->availabilityWarning->id === $expiredWarning->id;
        });

        Bus::assertNotDispatched(ActionExpiredAvailabilityWarningRemoval::class, function ($job) use ($nonExpiredWarning) {
            return $job->availabilityWarning->id === $nonExpiredWarning->id;
        });
    }

    #[Test]
    public function it_does_not_dispatch_for_already_expired_warnings(): void
    {
        Bus::fake();

        $trainingPlace = TrainingPlace::factory()->create();
        $check = AvailabilityCheck::factory()->failed()->create(['training_place_id' => $trainingPlace->id]);
        AvailabilityWarning::factory()->expired()->create([
            'training_place_id' => $trainingPlace->id,
            'availability_check_id' => $check->id,
            'expires_at' => now()->subDay(),
        ]);

        Artisan::call('training-places:check-for-expired-availability-warnings');

        Bus::assertNotDispatched(ActionExpiredAvailabilityWarningRemoval::class);
    }
}
